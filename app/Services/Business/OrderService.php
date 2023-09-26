<?php namespace App\Services\Business;

use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Http\Filters\OrderFilter;
use App\Models\Order;
use App\Models\OrderRawDataHistory;
use App\Models\Timesheet;
use App\Traits\Settingable;
use Carbon\Carbon;
use App\Traits\HasPaginator;
use Illuminate\Support\Collection;

class OrderService {
	use Settingable, HasPaginator;
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function get($request = null, $params = []) { // list | pagination | all (default)
		$queryParams = $request->only([
			'status',
			'search',
		]);
		
		$perPage = $this->getSettings('orders.per_page');
		$currentPage = $request->input('current_page', 1);
		
		$orderFilter = app()->make(OrderFilter::class, compact('queryParams'));
		
		$query = Order::filter($orderFilter)
			->when(isset($params['tied']) && $params['tied'] === true, function($query) {
				$query->tied();
			}, function($query) {
				$query->notTied();
			})
			->with('timesheets')
			->with('lastComment', function($query) {
				$query->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname');
			})
			->orderBy('id', 'desc');
		
		$paginate = $this->paginate($query, $currentPage, $perPage)->toArray();
		
		if ($paginate['data'] ?? false) {
			foreach ($paginate['data'] as $k => $row) {
				if (!isset($row['last_comment'])) continue;
				
				$userType = (int)$row['last_comment']['user_type'] ?? null;
				
				$paginate['data'][$k]['last_comment']['author'] = match($userType) {
					1		=> $row['last_comment']['author'] ?? null,
					2		=> $row['last_comment']['adminauthor'] ?? null,
					default	=> null,
				};
				
				unset($paginate['data'][$k]['last_comment']['adminauthor']);
			}
		}
		
		return match($params['data'] ?? 'all') {
			'all'			=> $this->_getAllFromPaginate($paginate),
			'list'			=> $this->_getDataFromPaginate($paginate),
			'pagination'	=> $this->_getInfoFromPaginate($paginate),
		};
	}
	
	
	
	
	
	
	
	
	
	/**
	 * @param integer $timesheetId
	 * @return Collection|null
	 */
	public function getToTimesheetList($timesheetId = null, $search = null):Collection|null {
		$list = Timesheet::find($timesheetId)
			->orders(function($query) use($search) {
				$query->where('order', 'LIKE', '%'.$search.'%');
			})
			/* ->withExists(['has_confirm_orders as is_confirmed' => function($q) use($timesheetId) { // это если нужно задать для конкретного заказа а для допранов - нет
				$q->where('confirmed_orders.timesheet_id', $timesheetId);
			}]) */
			->withExists(['has_confirm_orders as confirmed' => function($q) use($timesheetId) {
				$q->where('confirmed_orders.confirm', 0);
			}])
			->withExists(['has_confirm_orders as confirm' => function($q) use($timesheetId) {
				$q->where('confirmed_orders.confirm', 1);
			}])
			->withCount('rawDataHistory as rawDataHistory')
			//->withExists('has_confirm_orders as is_confirmed')
			->with('lastComment')
			->when($search, function ($query) use ($search) {
				return $query->where('order', 'LIKE', '%'.$search.'%');
			})
			->get();
		
		//logger($list->toArray());
		
		$statuses = OrderStatus::asFlippedArray();

		return $list->map(function($row) use($statuses) {
			$row['status'] = $row->pivot->doprun ? $statuses[OrderStatus::doprun] : ($statuses[$row['status']] ?? 0);
			$row['timesheet_id'] = $row->pivot->timesheet_id;
			return $row;
		});
	}
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function getToConfirmedList($type = 'actual') { // list | pagination | all (default)
		$list = Order::confirmed($type)
			->whereHas('timesheet_to_confirm')
			->with('timesheet_to_confirm', function($query) {
				$query->select('timesheet.id as timesheet_id', 'command_id', 'datetime');
				//$query->orderBy('datetime', 'ASC');
			})
			->with('lastComment', function($query) {
				$query->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname');
			})
			//->orderBy('datetime', 'desc')
			->get()
			->map(function($row) {
				$timesheetToConfirm = $row->timesheet_to_confirm->first();
				
				$row['from_id'] = $timesheetToConfirm?->pivot->from_id;
				$row['confirm'] = $timesheetToConfirm?->pivot->confirm;
				$row['date_add'] = $timesheetToConfirm?->pivot->date_add;
				$row['date_confirm'] = $timesheetToConfirm?->pivot->date_confirm;
				$row['command_id'] = $timesheetToConfirm?->command_id;
				$row['timesheet_id'] = $timesheetToConfirm?->timesheet_id;
				$row['datetime'] = $timesheetToConfirm?->datetime;
				
				unset($row['timesheet_to_confirm']);
				return $row;
			})
			->sortByDesc('datetime');
		
		
		/* 
		if ($paginate['data'] ?? false) {
			$statuses = $params['statuses'] ? OrderStatus::asFlippedArray() : null;
			
			foreach ($paginate['data'] as $k => $row) {
				if ($statuses) {
					$paginate['data'][$k]['status'] = $row['pivot']['doprun'] ? $statuses[OrderStatus::doprun] : ($statuses[$row['status']] ?? 0);
					$paginate['data'][$k]['timesheet_id'] = $row['pivot']['timesheet_id'];
					logger($paginate['data'][$k]);
				}
				
				if (!isset($row['last_comment'])) continue;
				
				$userType = (int)$row['last_comment']['user_type'] ?? null;
				
				$paginate['data'][$k]['last_comment']['author'] = match($userType) {
					1		=> $row['last_comment']['author'] ?? null,
					2		=> $row['last_comment']['adminauthor'] ?? null,
					default	=> null,
				};
				
				unset($paginate['data'][$k]['last_comment']['adminauthor']);
			}
		} */
		
		
		
		
		return $list;
	}
	
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function parse(?string $string) {
		//$arrData = $this->_removeEquals($string); // удалить "==="
		$arrData = $this->_removeQuotationMarks($string); // удалить "```"
		return $this->_getOrders($arrData);
	}
	
	
	
	
	
	
	
	
	
	
	/** получить комментарии заказа
	 * @param 
	 * @return 
	 */
	public function getComments($orderId = null) {
		if (is_null($orderId)) return false;
		$comments = Order::find($orderId)->comments()->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname')->get()->toArray();
		
		if (!$comments) return null;
		
		foreach ($comments as $k => $comment) {
			$comments[$k]['author'] = match($comment['user_type']) {
				1	=> $comment['author'] ?? null,
				2	=> $comment['adminauthor'] ?? null,
				default	=> null,
			};
			
			unset($comments[$k]['adminauthor']);
		}
		
		return $comments;
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function getRawDataHistory($orderId = null) {
		if (is_null($orderId)) return false;
		
		$history = Order::find($orderId)
			->rawDataHistory()
			->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname')
			->orderBy('id', 'DESC')
			->get()
			->toArray();
		
		if (!$history) return null;
		
		foreach ($history as $k => $row) {
			$history[$k]['author'] = match($row['user_type']) {
				1	=> $row['author'] ?? null,
				2	=> $row['adminauthor'] ?? null,
				default	=> null,
			};
			
			unset($history[$k]['adminauthor']);
		}
		
		return $history;
	}
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function setRawDataHistory($orderId = null, $data = null) {
		if (is_null($orderId)) return false;
		
		$diffData = diffStrings($data['data'] ?? '', $data['updated'] ?? '');
		
		$guard = getGuard();
		
		$userType = match($guard) {
			'site'	=> 1,
			'admin'	=> 2,
			default	=> 1,
		};
		
		$fromId = auth($guard)->user()->id;
		
		return OrderRawDataHistory::create([
			'order_id' => $orderId,
			'from_id' => $fromId,
			'user_type' => $userType,
			'data' => $diffData,
		]);
	}
	
	
	
	
	/** Задать статус заказа
	 * @param 
	 * @return 
	 */
	public function setStatus($orderId = null, $timesheetId = null, $status = null) {
		if (is_null($orderId) || is_null($status)) return false;
		
		$stat = OrderStatus::fromKey($status);
		
		$order = Order::find($orderId);
		$order->fill(['status' => $stat]);
		
		$timesheet = Timesheet::find($timesheetId);
		
		if (in_array($status, ['cancel', 'wait'])) {
			$timesheet->orders()->detach($orderId);
			eventLog()->orderDetach($order, $timesheetId, $status);
		} else {
			$timesheet->orders()->updateExistingPivot($orderId, ['doprun' => null]);
			
			if ($status == 'ready') {
				$now = DdrDateTime::now();
				$detachingData = ['from_id' => auth('site')->id(), 'date_add' => $now];
				
				// Если номер заказа начинается на #
				if ($isHashOrder = preg_match('/\#\w+/', $order?->order)) {
					$detachingData['confirmed_from_id'] = auth('site')->id();
					$detachingData['confirm'] = true;
					$detachingData['date_confirm'] = $now;
				}
				
				$timesheet->confirmOrders()->syncWithoutDetaching([$orderId => $detachingData]);
				eventLog()->orderToConfirm($order, $timesheetId, $detachingData);
			}
		}
		
		if (!$order->save()) return false;
		
		return $isHashOrder ? 'hash' : true;
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function setOrderType($order = null) {
		if (!$order) return null;
		
		if (!$rawData = $order['raw_data'] ?? false) return null;
		
		$patterns = $this->getSettings('orders_types');
		
		foreach ($patterns as $row) {
			$matches = explode("\n", $row['matches'] ?? null);
			$exceptions = explode("\n", $row['exceptions'] ?? null);
			$stat = true;
			
			if ($matches) {
				foreach ($matches as $match) {
					if (strpos($rawData, trim($match)) === false) {
						$stat = false;
						break;
					}
				}
			}
			
			if ($stat && $exceptions) {
				foreach ($exceptions as $exception) {
					if (strpos($rawData, trim($exception)) !== false) {
						$stat = false;
						break;
					}
				}
			}
			
			logger($stat ? 'stat true' : 'stat false');
			logger((int)$row['id'] ?? 'null');
			
			if ($stat) return (int)$row['id'] ?? null;
		}
		
		return null;
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	//--------------------------------------------------------------------------
	
	
	
	/**
	 * Получить заказы
	 * @param 
	 * @return  array 
	 */
	private function _getOrders(?array $array):array|null {
		$orders = array_filter($array, fn($item) => preg_match('/https?:\/\/.+/m', $item, $mathes) === 0);
		
		if (!$orders) return null;
		
		$link = $this->_perseLink($array);
		
		$data = [];
		foreach (array_values($orders) as $order) {
			preg_match('/&(amp;)?\w+/', $order, $matches);
			
			if (!$orderNumber = (count($matches) > 0 ? reset($matches) : null)) continue;
			
			$orderNumber = str_replace('&amp;', '&', $orderNumber);
			
			[$date, $tzId, $tzFormat] = $this->_parseDateTime($order);
			$price = $this->_persePrice($order);
			$serverName = $this->_perseServerName($order);
			
			$data[] = [
				'raw_data'		=> $this->_clearString($order),
				'order' 		=> $orderNumber ?? null,
				'server_name' 	=> $serverName ?? null,
				'link' 			=> $link ?? null,
				'price' 		=> $price ?? null,
				'date' 			=> $date,
				'timezone_id' 	=> $tzId,
				'tz_format'		=> $tzFormat,
			];
		}
		
		return $data ?? null;
	}
	
	
	
	
	
	
	
	/** Получить только список строк
	 * @param 
	 * @return 
	 */
	private function _getDataFromPaginate($paginate = null) {
		['data' => $data] = array_splice($paginate, array_search('data', array_keys($paginate)), 1);
		return $data ?? null;
	}
	
	
	/** Получить только инфу по пагинации
	 * @param 
	 * @return 
	 */
	private function _getInfoFromPaginate($paginate = null) {
		array_splice($paginate, array_search('data', array_keys($paginate)), 1);
		return $paginate ?? null;
	}
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _getAllFromPaginate($paginate = null) {
		['data' => $data] = array_splice($paginate, array_search('data', array_keys($paginate)), 1);
		return ['data' => $data ?? null, 'paginate' => $paginate ?? null];
	}
	
	
	
	
	
	/**
	 * Чистит строку
	 * @param 
	 * @return 
	 */
	public function _clearString(?string $str):string|null {
		$str = str_replace('&amp;', '&', $str);
		return $str;
	}
	
	
	
	
	/**
	 * Удаляет символы равно в начале и в конце
	 * @param 
	 * @return 
	 */
	private function _removeEquals(?string $str):array {
		$res = explode("\n", $str);
		$res = array_filter($res, fn($item) => preg_match('/\s*===\s*/mU', $item, $mathes) === 0);
		return $res;
	}
	
	
	
	/**
	 * Удаляет символы ``` в начале и в конце
	 * @param 
	 * @return 
	 */
	private function _removeQuotationMarks(?string $str):array {
		$clear = str_replace('```', '', $str);
		return array_filter(explode("\n", $clear));
	}
	
	
	
	
	
	
	/**
	 * Получить имя сервера
	 * @param 
	 * @return 
	 */
	private function _perseServerName(?string $order):string|null {
		preg_match('/\/inv\s.+-[^,]+/m', $order, $matches);
		if (!$matches) return null;
		return $matches[0] ? $matches[0] : null;
	}
	
	
	
	
	
	
	
	/**
	 * Получить ссылку
	 * @param 
	 * @return 
	 */
	private function _perseLink(?array $array):string|null {
		$link = array_filter($array, fn($item) => preg_match('/https?:\/\/.+/m', $item, $mathes) === 1);
		
		if (!$link = (count($link) > 0 ? reset($link) : null)) return null;
		
		$link = ltrim($link, '<');
		$link = rtrim($link, '>');
		
		return $link;
	}
	
	
	
	
	
	/**
	 * Получить стоимость
	 * @param 
	 * @return 
	 */
	private function _persePrice(?string $order):string|null {
		preg_match('/(\d{1,}.\d{1,2})\$/m', $order, $matches);
		if (!$matches) return null;
		return $matches[1] ? (float)$matches[1] : null;
	}
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _parseDateTime(?string $order):array|null {
		preg_match('/\B\(\w{2,3} (\d{1,2}) (\w{2,3}) @ (\d{1,2}:\d{1,2}) (\w{0,2}?)\s*(\w{2,4})\)\B/', $order, $matches);
		
		if (!$matches) return null;
		
		$month = $this->_getMonthVal($matches[2]);
		[$hour, $minute] = $this->_parseTime($matches[3], $matches[4]);
		
		$tzId = $this->_getTimeZoneId($matches[5]);
		
		$tzFormat = $this->_getTimeZoneFormat($matches[5]);
		
		$dt = Carbon::create(date('Y'), $month, $matches[1], $hour, $minute, 00);
		
		return [$dt, $tzId, $tzFormat];
	}
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _getMonthVal($mName = null) {
		return match($mName) {
			'Jan' => '01',
			'Feb' => '02',
			'Mar' => '03',
			'Apr' => '04',
			'May' => '05',
			'Jun' => '06',
			'Jul' => '07',
			'Aug' => '08',
			'Sep' => '09',
			'Oct' => '10',
			'Nov' => '11',
			'Dec' => '12',
		};
	}
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _parseTime($time = null, $meridiem = null) {
		[$hour, $minute] = explode(':', $time);
		
		$hour = match($meridiem) {
			'AM'	=> $hour == 12 ? 00 : $hour,
			'PM'	=> $hour + 12 == 24 ? 12 : $hour + 12,
			''		=> $hour,
		};
		
		return [$hour, $minute];
	}
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _getWeekNameVal($wName = null) {
		return match($wName) {
			'Mon' => 1,
			'Tue' => 2,
			'Wed' => 3,
			'Thu' => 4,
			'Fri' => 5,
			'Sat' => 6,
			'Sun' => 7,
		};
	}
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _getTimeZone($tzName = null) {
		$timezones = $this->getSettings('timezones', 'timezone', 'shift');
		return $timezones[$tzName];
	}


	/**
	 * @param 
	 * @return 
	 */
	private function _getTimeZoneId($tzName = null):int|null {
		$timezones = $this->getSettings('timezones', 'timezone', 'id');
		return (int)$timezones[$tzName] ?? null;
	}
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _getTimeZoneFormat($tzName = null):bool {
		$timezones = $this->getSettings('timezones', 'timezone', 'format_24');
		return !!$timezones[$tzName];
	}

	
	
}