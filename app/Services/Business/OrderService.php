<?php namespace App\Services\Business;

use App\Enums\OrderStatus;
use App\Http\Filters\OrderFilter;
use App\Models\Order;
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
	public function get($request = null, $dataType = 'all') { // data | pagination
		$queryParams = $request->only([
			'status',
		]);
		
		$perPage = $this->getSettings('orders.per_page');
		$currentPage = $request->input('current_page', 1);
		
		$orderFilter = app()->make(OrderFilter::class, compact('queryParams'));
		
		$query = Order::filter($orderFilter)->whereIn('orders.id', function ($builder) {
            $builder->select('timesheet_order.order_id')->from('timesheet_order');
        }, not: true)->orderBy('id', 'desc');
		
		
		$paginate = $this->paginate($query, $currentPage, $perPage)->toArray();
		
		return match($dataType) {
			'all'			=> $this->_getAllFromPaginate($paginate),
			'data'			=> $this->_getDataFromPaginate($paginate),
			'pagination'	=> $this->_getInfoFromPaginate($paginate),
		};
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
	
	
	
	
	
	
	
	
	/**
	 * @param integer $timesheetId
	 * @return Collection|null
	 */
	public function getToTimesheetList($timesheetId = null):Collection|null {
		$list = Timesheet::find($timesheetId)
			->orders()
			->with('lastComment', function($query) {
				$query->orderBy('created_at', 'desc');
			})
			->get();
		
		$statuses = OrderStatus::asFlippedArray();

		return $list->map(function($row) use($statuses) {
			$row['status'] = $row->pivot->doprun ? $statuses[OrderStatus::doprun] : ($statuses[$row['status']] ?? 0);
			$row['timesheet_id'] = $row->pivot->timesheet_id;
			return $row;
		});
	}
	
	
	
	
	
	
	
	
	
	
	/** получить комментарии заказа
	 * @param 
	 * @return 
	 */
	public function getComments($orderId = null) {
		if (is_null($orderId)) return false;
		return Order::find($orderId)->comments()->with('author:id,name,pseudoname')->get();
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
		$timesheet->orders()->syncWithPivotValues($orderId, ['doprun' => null]);
		
		return $order->save();
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
		preg_match('/\/inv\s\w+-\w+/m', $order, $matches);
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