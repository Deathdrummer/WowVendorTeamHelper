<?php namespace App\Http\Controllers\Business;

use App\Actions\AddOrderCommentAction;
use App\Actions\UpdateModelAction;
use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Order;
use App\Models\Timesheet;
use App\Services\Business\OrderService;
use App\Traits\HasPaginator;
use App\Traits\Renderable;
use App\Traits\Settingable;
use Illuminate\Http\Request;

class OrdersController extends Controller {
    use Renderable, HasPaginator, Settingable;
	
	protected $renderPath = 'site.section.orders.render';
	protected $orderService;
	
	
	public function __construct(OrderService $order) {
		$this->orderService = $order;
	}
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function list(Request $request) {
		['data' => $orders, 'paginate' => $paginate] = $this->orderService->get($request);
		
		$headers = [
			'current_page'	=> $paginate['current_page'] ?? null,
			'per_page'		=> $paginate['per_page'] ?? null,
			'last_page'		=> $paginate['last_page'] ?? null,
			'total'			=> $paginate['total'] ?? null,
		];
		
		$timezones = $this->getSettings('timezones', 'id');
		$status = $request->input('status', 1);
		
		$itemView = $this->renderPath.'.item';
		return $this->renderWithHeaders('list', compact('orders', 'itemView', 'timezones', 'status'), $headers);
	}
	
	
	
	
	
	
	/** Входящие заказы
	 * @param 
	 * @return 
	 */
	public function incoming_orders(Request $request) {
		['orders' => $orders, 'count_rows_in_list' => $cuntRowsInList, 'current_page' => $currentPage] = $request->all();
		
		$allNewOrdersCount = count($orders) ?? 0;
		$perPage = $this->getSettings('orders.per_page');

		$countRowsToSet = $cuntRowsInList - $perPage;
		
		if ($currentPage == 1) {
			$orders = array_slice($orders, 0, $perPage);
		} elseif ($countRowsToSet < 0) {
			$orders = array_slice($orders, $countRowsToSet, ($perPage - $cuntRowsInList));
		} elseif (count($orders) > $perPage) {
			$orders = array_slice($orders, -$perPage, $perPage);
		}
		
		$pagInfo = $this->orderService->get($request, 'pagination');
		
		$timezones = $this->getSettings('timezones', 'id');
		$status = OrderStatus::fromKey($request->input('status', 'new'))->value;
		
		$new = true;
		$itemView = $this->renderPath.'.item';
		$hasMoreOrders = !!($allNewOrdersCount - count($orders) > 0);
		return $this->renderWithHeaders('list', compact('orders', 'itemView', 'new', 'timezones', 'status'), ['orders_count' => count($orders), ...$pagInfo]);
	}
	
	
	
	
	
	
	
	
	
	/** Форма отправки заказа в лист ожидания
	 * @param 
	 * @return 
	 */
	public function to_wait_list_form(Request $request) {
		[
			'views'	=> $viewPath,
		] = $request->validate([
			'views'	=> 'required|string',
		]);

		return $this->render($viewPath);
	}
	
	
	
	/** Отправить заказ в лист ожидания
	 * @param 
	 * @return 
	 */
	public function to_wait_list(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'order_id'	=> $orderId,
			'message'	=> $message,
		] = $request->validate([
			'order_id'	=> 'required|numeric',
			'message'	=> 'required|string',
		]);
		
		$order = Order::find($orderId);
		$order->fill(['status' => -1]);
		$res = $order->save();
		
		// отправить коммент
		if ($message) {
			$addOrderComment($orderId, $message);
		}
		
		return response()->json($res);
	}
	
	
	
	
	
	
	
	/** Форма отправки заказа в отмененные
	 * @param 
	 * @return 
	 */
	public function to_cancel_list_form(Request $request) {
		[
			'views'	=> $viewPath,
		] = $request->validate([
			'views'	=> 'required|string',
		]);

		return $this->render($viewPath);
	}
	
	/** Отправить заказ в отмененные
	 * @param 
	 * @return 
	 */
	public function to_cancel_list(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'order_id'	=> $orderId,
			'message'	=> $message,
		] = $request->validate([
			'order_id'	=> 'required|numeric',
			'message'	=> 'required|string',
		]);
		
		$order = Order::find($orderId);
		$order->fill(['status' => -2]);
		$res = $order->save();
		
		// отправить коммент
		if ($message) {
			$addOrderComment($orderId, $message);
		}
		
		return response()->json($res);
	}
	
	
	
	
	
	
	/** Форма для перемещения заказа
	 * @param 
	 * @return 
	 */
	public function relocate_client(Request $request) {
		[
			'views'			=> $viewPath,
		] = $request->validate([
			'views'			=> 'required|string',
		]);
		
		
		return response()->view($viewPath.'.form');
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function get_relocate_timesheets_client(Request $request) {
		[
			'views'		=> $viewPath,
			'date'		=> $date,
			'order_id'	=> $orderId,
		] = $request->validate([
			'views'		=> 'required|string',
			'date'		=> 'required|date',
			'order_id'	=> 'required|numeric',
		]);
		
		if (!$order = Order::find($orderId)) return response()->json(false);
		
		$orderDate = $order?->date_msc;
		
		$timesheet = Timesheet::where('datetime', DdrDateTime::shift($orderDate, 'UTC'))->first();
		
		$timesheets = Timesheet::future($date)
			->withCount('orders AS orders_count')
			->orderBy('datetime', 'ASC')
			->get();
		
		$difficulties = $this->getSettingsCollect('difficulties')->mapWithKeys(function ($item, $key) {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		$eventsTypes = EventType::get()->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
		
		$commands = Command::get()->mapWithKeys(function ($item, $key)  {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		
		$headers = ['x-timesheet-id' => $timesheet?->id];
		return response()->view($viewPath.'.timesheets', compact('timesheets', 'commands', 'eventsTypes', 'orderDate'))->withHeaders($headers);
	}
	
	
	
	
	/** Переместить/Клонировать заказ
	 * @param 
	 * @return 
	 */
	public function set_relocate_client(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'comment'		=> $comment,
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
		] = $request->validate([
			'comment'		=> 'nullable|string',
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
		]);
		
		
		$timesheet = Timesheet::find($timesheetId);
		//$timesheet->orders()->detach([$orderId]);
		$sync = $timesheet->orders()->syncWithoutDetaching($orderId);
		
		if (!count($sync['attached'])) return response()->json(false);
		
		
		// менять статус на новый
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::new]);
		$res = $order->save();
		
		if (!$res) return response()->json(false);
		
		// отправить коммент
		if ($comment) {
			$addOrderComment($orderId, $comment);
		}
		
		return response()->json(true);
	}
	
	
	
	/** Переместить заказ
	 * @param 
	 * @return 
	 */
	private function _moveOrderClient($orderId = null, $timesheetId = null, $choosedTimesheetId = null) {
		$timesheet = Timesheet::find($timesheetId);
		$timesheet->orders()->detach([$orderId]);
		
		$choosedTimesheet = Timesheet::find($choosedTimesheetId);
		$sync = $choosedTimesheet->orders()->syncWithoutDetaching($orderId);
		//$timesheet->orders()->updateExistingPivot($orderId, ['doprun' => 1]);
		
		// менять статус на новый
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::new]);
		$res = $order->save();
		
		return (!!count($sync['attached']) && $res)  ? 'moved' : false;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------------------------------------ Для админ
	
	
	/** подгрузить список заказов выбранного события
	 * @param 
	 * @return 
	 */
	public function timesheet_list(Request $request) {
		[
			'views'			=> $viewPath,
			'timesheet_id'	=> $timesheetId,
		] = $request->validate([
			'views'			=> 'required|string',
			'timesheet_id'	=> 'required|numeric',
			'search'		=> 'exclude|nullable|string',
		]);
		
		$search = $request->input('search');
		
		$list = $this->orderService->getToTimesheetList($timesheetId, $search);
		
		$showType = $this->getSettings('order_statuses_showtype_list');
		
		$notifyButtons = $this->getSettings('slack_notifies');
		
		$statusesSettings = $this->getSettings('order_statuses');
		$timezones = $this->getSettings('timezones', 'id');
		$itemView = $viewPath.'.item';
		
		return response()->view($viewPath.'.list', compact('list', 'itemView', 'timezones', 'statusesSettings', 'showType', 'notifyButtons'));
	}
	
	
	
	
	
	
	/** форма ручного создания/обновления заказа в событии
	 * @param 
	 * @return 
	 */
	public function form(Request $request) {
		$action = $request->input('action', 'new');
		return match($action) {
			'new'	=> $this->_newForm($request),
			'edit'	=> $this->_editForm($request),
		};
	}
	
	
	
	
	/** форма ручного создания заказа в событии
	 * @param 
	 * @return 
	 */
	private function _newForm($request) {
		[
			'views'			=> $viewPath,
			'timesheet_id'	=> $timesheetId,
		] = $request->validate([
			'views'			=> 'required|string',
			'timesheet_id'	=> 'required|numeric',
		]);
		
		$timesheet = Timesheet::find($timesheetId);
		$timezones = $this->getSettings('timezones', 'id');
		
		$commandData = $timesheet->command->toArray();
		$timezone = $timezones[$commandData['region_id']];
		
		$date = $timesheet->datetime;
		$action = 'new';
		
		return response()->view($viewPath.'.form', compact('date', 'timezone', 'timezones', 'action'));
	}
	
	
	
	/** форма ручного редактирования заказа в событии
	 * @param 
	 * @return 
	 */
	private function _editForm($request) {
		[
			'views'		=> $viewPath,
			'order_id'	=> $ordertId,
		] = $request->validate([
			'views'		=> 'required|string',
			'order_id'	=> 'required|numeric',
		]);
		
		$order = Order::find($ordertId)->toArray();
		
		$order['action'] = 'edit';
		
		return response()->view($viewPath.'.form', [...$order]);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/** Создать заказ в событии
	 * @param 
	 * @return 
	 */
	public function save_form(Request $request, AddOrderCommentAction $addOrderComment) {
		$formData = $request->validate([
			'timesheet_id' 	=> 'required|numeric|exclude',
			'timezone_id' 	=> 'required|numeric',
			'date' 			=> 'required|date',
			'order' 		=> 'required|string',
			'price' 		=> 'required|decimal:0,2',
			'server_name' 	=> 'required|string',
			'raw_data' 		=> 'required|string',
			'link' 			=> 'string|nullable',
			'comment' 		=> 'string|nullable|exclude',
		]);
		
		$timezones = $this->getSettings('timezones', 'id');
		
		$shift = (int)$timezones[$formData['timezone_id']]['shift'];
		
		$formData['date'] = DdrDateTime::buildTimestamp($formData['date'], ['shift' => $shift]);
		
		['id' => $orderId] = Order::create($formData);
		
		$timesheetId = $request->input('timesheet_id');
		
		// Добавление комментария
		if ($comment = $request->input('comment')) {
			$addOrderComment($orderId, $comment);
		}
		
		$timesheet = Timesheet::find($timesheetId);
		$timesheet->orders()->attach([$orderId]);
		return response()->json(true);
	}
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function update_form(Request $request) {
		$formData = $request->validate([
			'order_id' 		=> 'required|numeric|exclude',
			'timesheet_id'	=> 'required|numeric|exclude',
			'order' 		=> 'required|string',
			'price' 		=> 'required|decimal:0,2',
			'server_name' 	=> 'required|string',
			'raw_data' 		=> 'required|string',
			'link' 			=> 'string|nullable',
		]);
		
		$orderId = $request->input('order_id');
		//$timesheetId = $request->input('timesheet_id');
		
		$order = Order::find($orderId);
		$order->fill($formData);
		$res = $order->save();
		
		
		//logger($order->timesheets);
		// Обновить поле doprun в промежуточной таблице
		//$timesheet = Timesheet::find($timesheetId);
		//$timesheet->orders()->updateExistingPivot($orderId, ['doprun' => 3]);
		
		return response()->json(!$res ? false : $formData);
	}
	
	
	
	
	
	
	/** Сформировать чат комментариев
	 * @param 
	 * @return 
	 */
	public function comments(Request $request) {
		[
			'views'		=> $viewPath,
			'order_id'	=> $orderId,
		] = $request->validate([
			'views'		=> 'required|string',
			'order_id'	=> 'required|numeric',
		]);
		
		$comments = $this->orderService->getComments($orderId);
		
		$itemView = $viewPath.'.chat.item';
		
		return response()->view($viewPath.'.chat.list', compact('comments', 'itemView', 'orderId'));
	}
	
	
	
	
	/** Отправить комментарий
	 * @param 
	 * @return 
	 */
	public function send_comment(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'views'		=> $viewPath,
			'order_id'	=> $orderId,
			'message'	=> $message,
		] = $request->validate([
			'views'		=> 'required|string',
			'order_id'	=> 'required|numeric',
			'message'	=> 'required|string',
		]);
		
		$comment = $addOrderComment($orderId, $message);
		
		return response()->view($viewPath.'.chat.item', [...$comment]);
	}
	
	
	
	
	
	
	/** Получить статусы
	 * @param 
	 * @return 
	 */
	public function statuses(Request $request) {
		[
			'views'		=> $viewPath,
			'order_id'	=> $orderId,
			'status'	=> $status,
		] = $request->validate([
			'views'		=> 'required|string',
			'order_id'	=> 'required|numeric',
			'status'	=> 'required|string',
		]);
		
		$orderStatusesSettings = $this->getSettingsCollect('order_statuses')->sortBy('sort')->where('show', 1);
		
		$currentStatusName = $status ?? 'new';
		
		$showType = $this->getSettings('order_statuses_showtype', 'color');
		
		return response()->view($viewPath.'.statuses', compact('orderStatusesSettings', 'orderId', 'currentStatusName', 'showType'));
	}
	
	
	
	
	
	
	
	/** Задать статус
	 * @param 
	 * @return 
	 */
	public function set_status(Request $request) {
		[
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
			'status'		=> $status,
		] = $request->validate([
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
			'status'		=> 'required|string',
		]);
		
		if (!$this->orderService->setStatus($orderId, $timesheetId, $status)) return response()->json(false);
		
		$orderStatusesSettings = $this->getSettingsCollect("order_statuses.{$status}");
		
		return response()->json($orderStatusesSettings);
	}
	
	
	
	
	
	
	
	
	/** Форма для перемещения заказа
	 * @param 
	 * @return 
	 */
	public function relocate(Request $request) {
		[
			'views'			=> $viewPath,
			//'order_id'		=> $orderId,
			//'timesheet_id'	=> $timesheetId,
			'type'			=> $type,
		] = $request->validate([
			'views'			=> 'required|string',
			//'order_id'		=> 'required|numeric',
			//'timesheet_id'	=> 'required|numeric',
			'type'			=> 'required|string',
		]);
		
		return response()->view($viewPath.'.form', compact('type'));
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function get_relocate_timesheets(Request $request) {
		[
			'views'			=> $viewPath,
			'date'			=> $date,
			'timesheet_id'	=> $timesheetId,
			'type'			=> $type,
		] = $request->validate([
			'views'			=> 'required|string',
			'date'			=> 'required|date',
			'timesheet_id'	=> 'required|numeric',
			'type'			=> 'required|string',
		]);
		
		
		$timesheets = Timesheet::future($date)
			->whereNot('id', $timesheetId)
			->withCount('orders AS orders_count')
			->orderBy('datetime', 'ASC')
			->get();
		
		$difficulties = $this->getSettingsCollect('difficulties')->mapWithKeys(function ($item, $key) {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		$eventsTypes = EventType::get()->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
		
		$commands = Command::get()->mapWithKeys(function ($item, $key)  {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		return response()->view($viewPath.'.timesheets', compact('timesheets', 'type', 'commands', 'eventsTypes'));
	}
	
	
	
	
	/** Переместить/Клонировать заказ
	 * @param 
	 * @return 
	 */
	public function set_relocate(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'comment'				=> $comment,
			'order_id'				=> $orderId,
			'timesheet_id'			=> $timesheetId,
			'choosed_timesheet_id'	=> $choosedTimesheetId,
			'type'					=> $type,
		] = $request->validate([
			'comment'				=> 'nullable|string',
			'order_id'				=> 'required|numeric',
			'timesheet_id'			=> 'required|numeric',
			'choosed_timesheet_id'	=> 'required|numeric',
			'type'					=> 'required|string',
		]);
		
		$stat = match($type) {
			'move'	=> $this->_moveOrder($orderId, $timesheetId, $choosedTimesheetId),
			'clone'	=> $this->_cloneOrder($orderId, $timesheetId, $choosedTimesheetId),
		};
		
		if (!$stat) return response()->json(false);
		
		// отправить коммент
		if ($comment) {
			$addOrderComment($orderId, $comment);
		}
		
		return response()->json(['stat' => $stat]);
	}
	
	
	
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function detach_form(Request $request) {
		[
			'views'			=> $viewPath,
		] = $request->validate([
			'views'			=> 'required|string',
		]);
		
		$lists = [
			OrderStatus::new 		=> 'Входящие',
			OrderStatus::wait 		=> 'Лист ожидания',
			OrderStatus::cancel 	=> 'Отмененные',
		];
		
		return response()->view($viewPath.'.unlink_order', compact('lists'));
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function detach(Request $request, UpdateModelAction $updateAction) {
		[
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
			'status'		=> $status,
		] = $request->validate([
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
			'status'		=> 'required|numeric',
		]);
		
		$timesheet = Timesheet::find($timesheetId);
		
		logger($status);
		
		if (!$res = $timesheet->orders()->detach($orderId)) return response()->json(false);
		
		$stat = $updateAction(Order::class, $orderId, ['status' => $status]);
		
		return response()->json($stat);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/** Переместить заказ
	 * @param 
	 * @return 
	 */
	private function _moveOrder($orderId = null, $timesheetId = null, $choosedTimesheetId = null) {
		$timesheet = Timesheet::find($timesheetId);
		$timesheet->orders()->detach([$orderId]);
		
		$choosedTimesheet = Timesheet::find($choosedTimesheetId);
		$sync = $choosedTimesheet->orders()->syncWithoutDetaching($orderId);
		//$timesheet->orders()->updateExistingPivot($orderId, ['doprun' => 1]);
		
		// менять статус на новый
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::new]);
		$res = $order->save();
		
		return (!!count($sync['attached']) && $res)  ? 'moved' : false;
	}
	
	
	
	/** Клонировать заказ (допран)
	 * @param 
	 * @return 
	 */
	private function _cloneOrder($orderId = null, $timesheetId = null, $choosedTimesheetId = null) {
		$choosedTimesheet = Timesheet::find($choosedTimesheetId);
		$choosedSync = $choosedTimesheet->orders()->syncWithoutDetaching([$orderId => ['doprun' => 1]]);
		
		if ($choosedSync['attached']) {
			$timesheet = Timesheet::find($timesheetId);
			$timesheet->orders()->syncWithoutDetaching([$orderId => ['doprun' => 1]]);
		}
		
		$res = match(true) {
			!!count($choosedSync['attached'] ?? [])	=> 'cloned',
			!!count($choosedSync['updated'] ?? [])	=> 'updated',
			default									=> false,
		};
		
		return $res;
	}
	
	
}