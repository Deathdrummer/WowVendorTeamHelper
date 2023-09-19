<?php namespace App\Services;

use App\Actions\LogEventAction;
use App\Enums\LogEventsGroups;
use App\Enums\LogEventsTypes;
use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Models\Command;
use App\Models\ConfirmedOrder;
use App\Models\EventType;
use App\Models\Order;
use App\Models\Timesheet;
use App\Models\TimesheetOrder;
use App\Models\TimesheetPeriod;
use App\Models\Traits\HasEvents;
use App\Models\User;
use App\Traits\Settingable;

class EventLogService {
	use Settingable, HasEvents;
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function timesheetCreated($timesheet) {
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
			
		$command = Command::find($timesheet?->command_id);
		$eventsTypes = $this->getEventsTypes();
		
		$info = [
			'id' => ['data' => $timesheet?->id ?? '-', 'title' => 'ID события'],
			'command_id' => ['data' => $command?->title ?? '-', 'title' => 'Команда'],
			'timesheet_period_id' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type_id' => ['data' => $eventsTypes[$timesheet?->event_type_id] ?? '-', 'title' => 'Тип события'],
			'datetime' => ['data' => $timesheet?->datetime ?? '-', 'title' => 'Дата и время', 'meta' => ['date' => 1]],
		];
		
		$this->sendToEventLog(LogEventsGroups::timesheets, LogEventsTypes::timesheetCreated, $info);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function timesheetUpdated($timesheet = null) {
		$eventsTypes = $this->getEventsTypes();
		$buildFields = self::buildFields($timesheet, ['event_type_id' => $eventsTypes], ['datetime']);
		
		$info = [
			'id' => $buildFields('id', 'ID события'),
			'command_id' => $buildFields('command_id', function($orig, $upd) {
				$row['data'] = Command::find($orig)?->title;
				if ($upd) $row['updated'] = Command::find($upd)?->title;
				return $row;
			}, 'Команда'),
			'timesheet_period_id' => $buildFields('timesheet_period_id', function($orig) {
				return ['data' => TimesheetPeriod::find($orig)?->title];
			}, 'Период'),
			'event_type_id'	=> $buildFields('event_type_id', 'Тип события'),
			'datetime'	=> $buildFields('datetime', 'Дата и время'),
		];
		
		$this->sendToEventLog(LogEventsGroups::timesheets, LogEventsTypes::timesheetUpdated, $info);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function timesheetRemoved($timesheet = null) {
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
			
		$command = Command::find($timesheet?->command_id);
		$eventsTypes = $this->getEventsTypes();
		
		$info = [
			'id' => ['data' => $timesheet?->id ?? '-', 'title' => 'ID события'],
			'command_id' => ['data' => $command?->title ?? '-', 'title' => 'Команда'],
			'timesheet_period_id' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type_id' => ['data' => $eventsTypes[$timesheet?->event_type_id] ?? '-', 'title' => 'Тип события'],
			'datetime' => ['data' => $timesheet?->datetime ?? '-', 'title' => 'Дата и время'],
		];
		
		$this->sendToEventLog(LogEventsGroups::timesheets, LogEventsTypes::timesheetRemoved, $info);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderCreated(Order $order, $timesheetId) {
		$timesheet = Timesheet::find($timesheetId);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		//$timezone?->format_24
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда'],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderCreatedInTs, $info);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderUpdated(Order $order, $timesheetId) {
		$buildFields = self::buildFields($order, datetimeFields: ['date', 'date_msc']);
		
		$timesheet = Timesheet::find($timesheetId);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		$timezone = $this->getTimezone($order?->timezone_id);
		
		$info = [
			'id' => $buildFields('id', 'ID заказа'),
			'order' => $buildFields('order', 'Номер заказа'),
			'price' => $buildFields('price', function($orig, $upd) {
				$row['data'] = number_format($orig, 2, '.', ' ');
				if ($upd) $row['updated'] = number_format($upd, 2, '.', ' ');
				$row['meta'] = ['symbal' => 1];
				return $row;
			}, 'Стоимость'),
			'server_name' => $buildFields('server_name', 'Инвайт'),
			'raw_data' => $buildFields('raw_data', 'Данные'),
			'link' => $buildFields('link', 'Ссылка'),
			'date' => $buildFields('date', 'Дата и время ориг.'),
			'date_msc' => $buildFields('date', function($orig, $upd) use($order) {
				$timezoneId = $order?->timezone_id ?? null;
				$timezones = $this->getSettings('timezones', 'id', 'shift');
				$shift = (-1 * (int)$timezones[$timezoneId]);
				
				$row['data'] = DdrDateTime::shift($orig, $shift);
				if ($upd) $row['updated'] = DdrDateTime::shift($upd, $shift);
				$row['meta'] = ['date' => 1];
				return $row;
			}, 'Дата и время МСК'),

			'timezone' => ['data' => $timezone['timezone'] ?? '-', 'title' => 'Временная зона'],
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderUpdated, $info);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToWaitlList(Order $order) {
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		$oldData = $order?->getRawOriginal() ?? null;
		
		$orderStatuses = $this->getSettings('order_statuses');
		
		$oldStatus = $orderStatuses[OrderStatus::fromValue((int)$oldData['status'])?->key]['name'] ?? '-';
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			'old_status' => ['data' => $oldStatus, 'title' => 'Предыдущий статус'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderToWaitlList, $info);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToCancelList(Order $order) {
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$oldData = $order?->getRawOriginal() ?? null;
		$orderStatuses = $this->getSettings('order_statuses');
		$oldStatus = $orderStatuses[OrderStatus::fromValue((int)$oldData['status'])?->key]['name'] ?? '-';
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			'old_status' => ['data' => $oldStatus, 'title' => 'Предыдущий статус'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderToCancelList, $info);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderAttach(Order $order, $timesheetId) {
		$timesheet = Timesheet::find($timesheetId);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$oldData = $order?->getRawOriginal() ?? null;
		$orderStatuses = $this->getSettings('order_statuses');
		$oldStatus = $orderStatuses[OrderStatus::fromValue((int)$oldData['status'])?->key]['name'] ?? '-';
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			'old_status' => ['data' => $oldStatus, 'title' => 'Предыдущий статус'],
			
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderAttach, $info);
		
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderMove(Order $order, $oldStatus, $oldTimesheetId, $newTimesheetId) {
		$oldTimesheet = Timesheet::find($oldTimesheetId);
		$newTimesheet = Timesheet::find($newTimesheetId);
		
		$oldCommand = $this->getTsCommand($oldTimesheet?->command_id);
		$newCommand = $this->getTsCommand($newTimesheet?->command_id);
		
		$oldTimesheetPeriod = TimesheetPeriod::find($oldTimesheet?->timesheet_period_id);
		
		$oldEventType = $this->getTsEventType($oldTimesheet?->event_type_id);
		$newEventType = $this->getTsEventType($newTimesheet?->event_type_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$orderStatuses = $this->getSettings('order_statuses');
		$isConfirmed = ConfirmedOrder::where('order_id', $order?->id)->count();
		$oldStatus = $isConfirmed ? 'На подтверждении' : $orderStatuses[OrderStatus::fromValue((int)$oldStatus)?->key]['name'] ?? '-';
		//$newStatus = $isConfirmed ? 'На подтверждении' : $orderStatuses[OrderStatus::fromValue((int)$order?->status)?->key]['name'] ?? '-';
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			'statuses' => ['data' => $oldStatus, 'title' => 'Статус заказа'],
			
			'command' => ['data' => $oldCommand?->title ?? '-', 'updated' => $newCommand?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $oldTimesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $oldEventType ?? '-', 'updated' => $newEventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderMove, $info);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderDoprun($orderId, $oldTimesheetId, $newTimesheetId) {
		$order = Order::find($orderId);
		
		$oldTimesheet = Timesheet::find($oldTimesheetId);
		$newTimesheet = Timesheet::find($newTimesheetId);
		
		$oldCommand = $this->getTsCommand($oldTimesheet?->command_id);
		$newCommand = $this->getTsCommand($newTimesheet?->command_id);
		
		$oldTimesheetPeriod = TimesheetPeriod::find($oldTimesheet?->timesheet_period_id);
		
		$oldEventType = $this->getTsEventType($oldTimesheet?->event_type_id);
		$newEventType = $this->getTsEventType($newTimesheet?->event_type_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			
			'command' => ['data' => $oldCommand?->title ?? '-', 'updated' => $newCommand?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $oldTimesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $oldEventType ?? '-', 'updated' => $newEventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderDoprun, $info);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToConfirm($order, $timesheetId, $detachingData) {
		$from = User::find($detachingData['from_id']);
		
		$timesheet = Timesheet::find($timesheetId);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			
			'from' => ['data' => $from['name'] ?? $from['pseudoname'], 'title' => 'Отправил на проверку'],
			
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderToConfirm, $info);
		
	}
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderConfirm(ConfirmedOrder $confirmedOrder) {
		$order = Order::find($confirmedOrder?->order_id);
		
		$from = User::find($confirmedOrder?->from_id);
		$confirmedFrom = User::find($confirmedOrder?->confirmed_from_id);
		
		$timesheet = Timesheet::find($confirmedOrder?->timesheet_id);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			
			'from' => ['data' => $from['name'] ?? $from['pseudoname'], 'updated' => $confirmedFrom['name'] ?? $confirmedFrom['pseudoname'], 'title' => 'Кем создан / подтвержден'],
			
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderConfirm, $info);
	}
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function ordersConfirm($rows) {
		$ordersIds = $rows?->pluck('order_id')?->join(', ');
		$orders = Order::whereIn('id', $rows?->pluck('order_id'))->pluck('order')?->join(', ');
		
		$confirmedFrom = auth()->user();
		
		$info = [
			'id' => ['data' => $ordersIds ?? '-', 'title' => 'ID заказов'],
			'order' => ['data' => $orders ?? '-', 'title' => 'Номера заказов'],
			'from' => ['data' => $confirmedFrom['name'] ?? $confirmedFrom['pseudoname'], 'title' => 'Кем подтверждены'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::ordersConfirm, $info);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderRemoveFromConfirmed(Order $order) {
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderRemoveFromConfirmed, $info);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderDetach(Order $order, $timesheetId, $status) {
		$timesheet = Timesheet::find($timesheetId);
		$command = $this->getTsCommand($timesheet?->command_id);
		$eventType = $this->getTsEventType($timesheet?->event_type_id);
		$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
		
		$timezone = $this->getTimezone($order?->timezone_id);
		$dateMsc = DdrDateTime::shift($order?->date, (-1 * $timezone['shift']));
		
		$oldData = $order?->getRawOriginal() ?? null;
		$orderStatuses = $this->getSettings('order_statuses');
		$tsOrder = TimesheetOrder::where('order_id', $order?->id)->first();
		
		$oldStatus = $tsOrder?->doprun == 1 ? ($orderStatuses['doprun']['name'] ?? '-') : ($orderStatuses[OrderStatus::fromValue($oldData['status'])?->key]['name'] ?? '-');
		
		$info = [
			'id' => ['data' => $order?->id ?? '-', 'title' => 'ID заказа'],
			'order' => ['data' => $order?->order ?? '-', 'title' => 'Номер заказа'],
			'price' => ['data' => number_format($order?->price, 2, '.', ' ')  ?? '-', 'title' => 'Стоимость', 'meta' => ['symbal' => 1]],
			'server_name' => ['data' => $order?->server_name ?? '-', 'title' => 'Инвайт'],
			'raw_data' => ['data' => $order?->raw_data ?? '-', 'title' => 'Данные'],
			'link' => ['data' => $order?->link ?? '-', 'title' => 'Ссылка'],
			'date' => ['data' => $order?->date ?? '-', 'title' => 'Дата и время ориг.', 'meta' => ['date' => 1]],
			'date_msc' => ['data' => $dateMsc, 'title' => 'Дата и время МСК', 'meta' => ['date' => 1]],
			'timezone' => ['data' => $timezone['timezone'], 'title' => 'Временная зона'],
			'status' => ['data' => $oldStatus, 'title' => 'Предыдущий статус'],
			'new_status' => ['data' => $orderStatuses[$status]['name'] ?? '-', 'title' => 'Новый статус'],
			
			'command' => ['data' => $command?->title ?? '-', 'title' => 'Команда', 'sort' => 10],
			'timesheet_period' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
			'event_type' => ['data' => $eventType ?? '-', 'title' => 'Тип события'],
		];
		
		$this->sendToEventLog(LogEventsGroups::orders, LogEventsTypes::orderDetach, $info);
	}
	
	






	
	
	
	
	
	
	//----------------------------------------------------------------------------------------------------------------------
	
	private function getEventsTypes():array {
		$eventType = app()->make(EventType::class);
		$difficulties = $this->getSettings('difficulties', 'id', 'title');
		return $eventType->get()?->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
	}
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function getTimezone($timezoneId = null) {
		$timezones = $this->getSettings('timezones', 'id', null, ['id' => $timezoneId]);
		return $timezones[$timezoneId] ?? false;
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function getTsEventType($eventTypeId = null) {
		if (!$eventTypeId) return false;
		if (!$eventsType = EventType::find($eventTypeId)) return false;
		$difficulties = $this->getSettings('difficulties', 'id', 'title', ['id' => $eventsType['difficult_id']]);
		return $eventsType['title'].'-'.$difficulties[$eventsType['difficult_id']];
	}
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function getTsCommand($commandId = null) {
		if (!$commandId) return false;
		if (!$command = Command::find($commandId)) return false;
		return $command;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//----------------------------------------------------------------------------------------------------------------------
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function sendToEventLog(...$params) {
		$eventLog = app()->make(LogEventAction::class);
		return $eventLog(...$params);
	}
	
}