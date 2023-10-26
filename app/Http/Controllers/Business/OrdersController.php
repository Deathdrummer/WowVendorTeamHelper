<?php namespace App\Http\Controllers\Business;

use App\Actions\AddOrderCommentAction;
use App\Actions\SendSlackMessageAction;
use App\Actions\UpdateModelAction;
use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\ConfirmedOrder;
use App\Models\EventType;
use App\Models\Order;
use App\Models\Timesheet;
use App\Models\TimesheetOrder;
use App\Services\Business\OrderService;
use App\Services\EventLogService;
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
		$status = $request->input('status', 'new');
		
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
		
		$pagInfo = $this->orderService->get($request, ['data' => 'pagination']);
		
		$timezones = $this->getSettings('timezones', 'id');
		$status = OrderStatus::fromKey($request->input('status', 'new'))->value;
		
		$new = true;
		$itemView = $this->renderPath.'.item';
		$hasMoreOrders = !!($allNewOrdersCount - count($orders) > 0);
		return $this->renderWithHeaders('list', compact('orders', 'itemView', 'new', 'timezones', 'status'), ['orders_count' => count($orders), ...$pagInfo]);
	}
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function confirmed_orders(Request $request) {
		[
			'type'	=> $type,
			'views'	=> $viewPath,
		] = $request->validate([
			'type'	=> 'required|string',
			'views'	=> 'required|string',
		]);
		
		$list = $this->orderService->getToConfirmedList($type);
		
		$notifyButtons = $this->getSettings('slack_notifies');
		$showType = $this->getSettings('order_statuses_showtype_list');
		$statusesSettings = $this->getSettings('order_statuses');
		$timezones = $this->getSettings('timezones', 'id');
		
		$commands = Command::get()?->mapWithKeys(function ($item, $key) use($timezones) {
    		$item['timezone'] = $timezones[$item['region_id']]['timezone'] ?? '-';
    		$item['shift'] = $timezones[$item['region_id']]['shift'];
    		$item['format_24'] = $timezones[$item['region_id']]['format_24'] ?? 0;
			return [$item['id'] => $item];
		})->toArray();
		
		$itemView = $viewPath.'.item';
		
		return response()
			->view($viewPath.'.list', compact('list', 'itemView', 'timezones', 'statusesSettings', 'commands', 'showType', 'notifyButtons', 'type'))
			->withHeaders(['orders_count' => count($list)]);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function confirm_order(Request $request, UpdateModelAction $updateAction, SendSlackMessageAction $sendMessage) {
		[
			'order_id'	=> $orderId,
		] = $request->validate([
			'order_id'	=> 'required|numeric',
		]);
		
		$response = $updateAction(ConfirmedOrder::class, ['order_id' => $orderId], ['confirmed_from_id' => auth('site')?->user()?->id, 'confirm' => true, 'date_confirm' => DdrDateTime::now()], returnModel: true);
		
		if (!$response) return response()->json(false);
		
		eventLog()->orderConfirm($response);
		
		$data = $this->getSettings('confirm_orders');
		
		$sendMassResp = $sendMessage([
			'order_id' => $orderId,
			'webhook' => $data['webhook'] ?? null,
			'message' => $data['message'] ?? null,
		]);
		
		// Доп. проверка отправки уведомления.
		// Если не отправилось - то статус подтверждения отменяется
		if (!$sendMassResp) {
			$updateAction(ConfirmedOrder::class, ['order_id' => $orderId], ['confirmed_from_id' => null, 'confirm' => false, 'date_confirm' => null]);
			return response()->json(false);
		}
		
		return response()->json($response && $sendMassResp);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function confirm_all_orders(SendSlackMessageAction $sendMessage) {
		$query = ConfirmedOrder::whereNot('confirm', 1);
		
		$rows = $query->get();
		$ordersIds = $rows->pluck('order_id')->toArray();
		
		$response = $query->update([
			'confirmed_from_id' => auth('site')?->user()?->id,
			'confirm' => true,
			'date_confirm' => DdrDateTime::now()
		]);
		
		if (!$response) return response()->json(false);
		
		eventLog()->ordersConfirm($rows);
		
		$data = $this->getSettings('confirm_orders');
		
		$sendMassResp = $sendMessage([
			'order_id' 	=> $ordersIds,
			'webhook' 	=> $data['webhook'] ?? null,
			'message' 	=> $data['message'] ?? null,
		]);
		
		// Доп. проверка отправки уведомления.
		// Если не отправилось - то статус подтверждения отменяется
		if (!$sendMassResp) {
			$toBackQuery = ConfirmedOrder::whereIn('order_id', $ordersIds);
			$toBackQuery->update([
				'confirmed_from_id' => null,
				'confirm' => false,
				'date_confirm' => null
			]);
			
			return response()->json(false);
		}
		
		return response()->json($response && $sendMassResp== 'ok');
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function remove_order_from_confirmed(Request $request, UpdateModelAction $updateModel) {
		[
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
		] = $request->validate([
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
		]);
		
		if (!$deleted = ConfirmedOrder::where('order_id', $orderId)->delete()) return response()->json(false);
		
		TimesheetOrder::where('order_id', $orderId)->update(['doprun' => null]);
		
		$res = $updateModel(Order::class, $orderId, ['status' => OrderStatus::new], returnModel: true);
		
		eventLog()->orderRemoveFromConfirmed($res);
		
		return response()->json($res);
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

		return $this->render($viewPath, ['listType' => 'лист ожидания']);
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
			'message'	=> 'string|nullable',
		]);
		
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::wait]);
		eventLog()->orderToWaitlList($order);
		$res = $order->save();
		
		// отправить коммент
		if ($message) {
			$addOrderComment($orderId, $message);
		}
		
		return response()->json($res);
	}
	
	
	
	
	
	
	/** Форма отправки заказа в лист ожидания
	 * @param 
	 * @return 
	 */
	public function to_necro_list_form(Request $request) {
		[
			'views'	=> $viewPath,
		] = $request->validate([
			'views'	=> 'required|string',
		]);

		return $this->render($viewPath, ['listType' => 'некроту']);
	}
	
	
	
	/** Отправить заказ в лист ожидания
	 * @param 
	 * @return 
	 */
	public function to_necro_list(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'order_id'	=> $orderId,
			'message'	=> $message,
		] = $request->validate([
			'order_id'	=> 'required|numeric',
			'message'	=> 'string|nullable',
		]);
		
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::necro]);
		eventLog()->orderToWaitlList($order);
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

		return $this->render($viewPath, ['listType' => 'отмененные']);
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
			'message'	=> 'string|nullable',
		]);
		
		$order = Order::find($orderId);
		$order->fill(['status' => OrderStatus::cancel]);
		eventLog()->orderToCancelList($order);
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
			'order_id'	=> $orderId,
			'views'		=> $viewPath,
		] = $request->validate([
			'order_id'	=> 'required|numeric',
			'views'		=> 'required|string',
		]);
		

		$order = Order::find($orderId);
		
		$rawData = $order?->raw_data;
		
		$regionId = null;
		$timezone = $this->_parseTimezone($rawData);
		if ($timezone) {
			$timezones = $this->getSettings('timezones', null, null, ['timezone' => $timezone]);
			$regionId = reset($timezones)['region'];
		}
		
		$regions = $this->getSettings('regions', 'id', 'title');
		
		return response()->view($viewPath.'.form', compact('regions', 'regionId', 'rawData'));
	}
	
	
	
	
	
	/** Получить список событий для формы привязки заказа
	 * @param 
	 * @return 
	 */
	public function get_relocate_timesheets_client(Request $request) {
		[
			'views'		=> $viewPath,
			'date'		=> $calendarDate,
			'region_id'	=> $regionId,
			'period'	=> $period,
			'order_id'	=> $orderId,
		] = $request->validate([
			'views'		=> 'required|string',
			'date'		=> 'required|date',
			'region_id'	=> 'numeric|nullable',
			'period'	=> 'string|nullable',
			'order_id'	=> 'required|numeric',
		]);
		
		
		if (!$order = Order::find($orderId)) return response()->json(false);
		
		$regionShiftHours = $this->getSettings('regions', 'id', 'shift', ['id' => $regionId])[$regionId] ?? 0;
		
		$orderDate = $order?->date_msc;
		
		$timezonesIds = $this->getSettingsCollect('timezones', null, null, ['region' => $regionId])->pluck('id');
		$commandsIds = Command::whereIn('region_id', $timezonesIds)->get()->pluck('id');
		
		$tsQuery = function() use($period, $calendarDate, $orderDate) {
			return match($period) {
				'actual'	=> Timesheet::future($calendarDate, $orderDate),
				'past'		=> Timesheet::past($calendarDate, $orderDate),
				default		=> Timesheet::future($calendarDate, $orderDate),
			};
		};
		
		$timesheet = $tsQuery()
			->where('datetime', DdrDateTime::shift($orderDate, 'UTC'))
			->whereIn('command_id', $commandsIds)
			->first();
		
		$timesheets = $tsQuery()->withCount('orders AS orders_count')
			->whereIn('command_id', $commandsIds)
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
		
		$choosedTsId = $timesheet?->id;
		$headers = ['x-timesheet-id' => $choosedTsId];
		
		return response()->view($viewPath.'.timesheets', compact('timesheets', 'commands', 'eventsTypes', 'orderDate', 'choosedTsId', 'regionShiftHours'))->withHeaders($headers);
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
		eventLog()->orderAttach($order, $timesheetId);
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
		
		$orderStatusesSettings = $this->getSettingsCollect('order_statuses');
		$canAnySetStat = auth('site')->user()->canany([...array_values($orderStatusesSettings->map(fn($stat, $statName) => $statName.'-status:site')->toArray())]);
		
		$statusesSettings = $this->getSettings('order_statuses');
		$timezones = $this->getSettings('timezones', 'id');
		$itemView = $viewPath.'.item';
		
		
		return response()->view($viewPath.'.list', compact('list', 'itemView', 'timezones', 'statusesSettings', 'showType', 'notifyButtons', 'canAnySetStat', 'timesheetId'));
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
		
		$ordersTypes = $this->getSettings('orders_types', 'id', 'title');
		
		$date = $timesheet->datetime;
		$action = 'new';
		
		return response()->view($viewPath.'.form', compact('date', 'timezone', 'timezones', 'ordersTypes', 'action'));
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
		
		$ordersTypes = $this->getSettings('orders_types', 'id', 'title');
		
		$order['action'] = 'edit';
		
		return response()->view($viewPath.'.form', [...$order, 'ordersTypes' => $ordersTypes]);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/** Создать заказ в событии
	 * @param 
	 * @return 
	 */
	public function save_form(Request $request, AddOrderCommentAction $addOrderComment, EventLogService $eventLog) {
		$formData = $request->validate([
			'timesheet_id' 	=> 'required|numeric|exclude',
			'timezone_id' 	=> 'required|numeric',
			'date' 			=> 'required|date',
			'order' 		=> 'required|string',
			'order_type' 	=> 'required|numeric',
			'price' 		=> 'required|decimal:0,2',
			'server_name' 	=> 'required|string',
			'raw_data' 		=> 'required|string',
			'link' 			=> 'string|nullable',
			'comment' 		=> 'string|nullable|exclude',
		]);
		
		$timezones = $this->getSettings('timezones', 'id');
		
		$shift = (int)$timezones[$formData['timezone_id']]['shift'];
		
		$formData['date'] = DdrDateTime::buildTimestamp($formData['date'], ['shift' => $shift]);
		
		$order = Order::create($formData);
		
		$timesheetId = $request->input('timesheet_id');
		
		// Добавление комментария
		if ($comment = $request->input('comment')) {
			$addOrderComment($order['id'], $comment);
		}
		
		$timesheet = Timesheet::find($timesheetId);
		$timesheet->orders()->attach([$order['id']]);
		
		$eventLog->orderCreated($order, $timesheetId);
		
		return response()->json(true);
	}
	
	
	
	
	
	
	/** Обновить заказ в событии
	 * @param 
	 * @return 
	 */
	public function update_form(Request $request, EventLogService $eventLog) {
		$formData = $request->validate([
			'order_id' 		=> 'required|numeric|exclude',
			'timesheet_id'	=> 'required|numeric|exclude',
			'order' 		=> 'required|string',
			'order_type' 	=> 'numeric|nullable',
			'ot_orig' 		=> 'numeric|nullable|exclude',
			'price' 		=> 'required|decimal:0,2',
			'server_name' 	=> 'required|string',
			'raw_data' 		=> 'required|string',
			'link' 			=> 'string|nullable',
		]);
		
		$orderId = $request->input('order_id');
		$timesheetId = $request->input('timesheet_id');
		
		if ($request->input('ot_orig') != $formData['order_type']) {
			$formData['ot_changed'] = true;
		}
		
		$order = Order::find($orderId);
		$order->fill($formData);
		$eventLog->orderUpdated($order, $timesheetId);
		$res = $order->save();
		
		$orderWithCount = $order->loadCount('rawDataHistory as rawDataHistory');
		$formData['rawDataHistory'] = $orderWithCount?->rawDataHistory ?? 0;
		
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
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function rawdatahistory(Request $request) {
		[
			'views'		=> $viewPath,
			'order_id'	=> $orderId,
		] = $request->validate([
			'views'		=> 'required|string',
			'order_id'	=> 'required|numeric',
		]);
		
		$history = $this->orderService->getRawDataHistory($orderId);
		
		return response()->view($viewPath.'.raw_data_history', compact('history', 'orderId'));
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
	public function set_status(Request $request, AddOrderCommentAction $addOrderComment) {
		[
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
			'status'		=> $status,
			'message'		=> $message,
		] = $request->validate([
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
			'status'		=> 'required|string',
			'message'		=> 'string|nullable',
		]);
		
		if (!$setStatRes = $this->orderService->setStatus($orderId, $timesheetId, $status)) return response()->json(false);
		
		// отправить коммент
		if ($message) {
			$addOrderComment($orderId, $message);
		}
		
		$orderStatusesSettings = $this->getSettingsCollect("order_statuses.{$status}");
		
		return response()->json([...$orderStatusesSettings, 'isHash' => ($setStatRes === 'hash')]);
	}
	
	
	
	
	
	
	
	
	/** Форма для перемещения заказа
	 * @param 
	 * @return 
	 */
	public function relocate(Request $request) {
		[
			'views'			=> $viewPath,
			'order_id'		=> $orderId,
			//'timesheet_id'	=> $timesheetId,
			'type'			=> $type,
		] = $request->validate([
			'views'			=> 'required|string',
			'order_id'		=> 'required|numeric',
			//'timesheet_id'	=> 'required|numeric',
			'type'			=> 'required|string',
		]);
		
		
		$order = Order::find($orderId);
		
		$rawData = $order?->raw_data;
		
		$regionId = null;
		$timezone = $this->_parseTimezone($rawData);
		if ($timezone) {
			$timezones = $this->getSettings('timezones', null, null, ['timezone' => $timezone]);
			$regionId = reset($timezones)['region'];
		}
		
		$regions = $this->getSettings('regions', 'id', 'title');
		
		
		return response()->view($viewPath.'.form', compact('type', 'regions', 'regionId', 'rawData'));
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function get_relocate_timesheets(Request $request) {
		[
			'views'			=> $viewPath,
			'date'			=> $date,
			'region_id'		=> $regionId,
			'period'		=> $period,
			'timesheet_id'	=> $timesheetId,
			'type'			=> $type,
		] = $request->validate([
			'views'			=> 'required|string',
			'date'			=> 'required|date',
			'region_id'		=> 'numeric|nullable',
			'period'		=> 'string|nullable',
			'timesheet_id'	=> 'required|numeric',
			'type'			=> 'required|string',
		]);
		
		$regionShiftHours = $this->getSettings('regions', 'id', 'shift', ['id' => $regionId])[$regionId] ?? 0;
		
		$timezones = $this->getSettingsCollect('timezones')->where('region', $regionId)->pluck('id')->toArray();
		$commandsIds = Command::whereIn('region_id', $timezones)->get()->pluck('id');
		
		$tsQuery = match($period) {
			'actual'	=> Timesheet::future($date),
			'past'		=> Timesheet::past($date),
			default		=> Timesheet::future($date),
		};
		
		$timesheets = $tsQuery->withCount('orders AS orders_count')
			->whereIn('command_id', $commandsIds)
			->whereNot('id', $timesheetId)
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
		
		return response()->view($viewPath.'.timesheets', compact('timesheets', 'type', 'commands', 'eventsTypes', 'regionShiftHours'));
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
		
		if (!$res = $timesheet->orders()->detach($orderId)) return response()->json(false);
		
		ConfirmedOrder::where('order_id', $orderId)->delete();
		
		$order = $updateAction(Order::class, $orderId, ['status' => $status], function($order) use($timesheetId, $status) {
			eventLog()->orderDetach($order, $timesheetId, OrderStatus::fromValue((int)$status)?->key ?? 'new');
		});
		
		return response()->json(!!$order);
	}
	
	
	
	
	
	
	
	
	
	//------------------------------------------------------------------------------------------------------------------
	
	
	
	
	
	/**
	* 
	* @param string|null $orderRawData
	* @return string|null timezone
	*/
	private function _parseTimezone(?string $orderRawData = null) {
		if (!$orderRawData) return null;
		preg_match('/\B\(\w{2,3} (\d{1,2}) (\w{2,3}) @ (\d{1,2}:\d{1,2}) (\w{0,2}?)\s*(\w{2,4})\)\B/', $orderRawData, $matches);
		if (!$matches) return null;
		return $matches[5] ?? null;
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
		$oldStatus = $order?->status;
		$res = $order->save();
		
		$movedStat = (!!count($sync['attached']) && $res) ? 'moved' : false;
		
		if ($movedStat) eventLog()->orderMove($order, $oldStatus, $timesheetId, $choosedTimesheetId);
		
		return $movedStat;
	}
	
	
	
	/** Клонировать заказ (допран)
	 * @param 
	 * @return 
	 */
	private function _cloneOrder($orderId = null, $timesheetId = null, $choosedTimesheetId = null) {
		$choosedTimesheet = Timesheet::find($choosedTimesheetId);
		$choosedSync = $choosedTimesheet->orders()->syncWithoutDetaching([$orderId => ['doprun' => 1, 'cloned' => 1]]);
		
		if ($choosedSync['attached']) {
			$timesheet = Timesheet::find($timesheetId);
			$timesheet->orders()->syncWithoutDetaching([$orderId => ['doprun' => 1]]);
		}
		
		$res = match(true) {
			!!count($choosedSync['attached'] ?? [])	=> 'cloned',
			!!count($choosedSync['updated'] ?? [])	=> 'updated',
			default									=> false,
		};
		
		if ($res) eventLog()->orderDoprun($orderId, $timesheetId, $choosedTimesheetId);
		
		return $res;
	}
	
	
}