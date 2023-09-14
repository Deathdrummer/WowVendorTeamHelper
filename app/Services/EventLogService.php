<?php namespace App\Services;

use App\Models\ConfirmedOrder;
use App\Models\Order;

class EventLogService {
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderCreated(Order $order) {
		logger('orderCreated');
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderUpdated(Order $order) {
		logger('orderUpdated');
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderSetStatus(Order $order) {
		logger('orderSetStatus');
		//logger($order);
	}
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToWaitlList(Order $order) {
		logger('orderToWaitlList');
		//logger($order);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToCancelList(Order $order) {
		logger('orderToCancelList');
		//logger($order);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderAttach(Order $order, $timesheetId) {
		// у order изначальный статус может быть разным, так как привязка может быть не только из входящих, но и из листа ожидания или отмененных
		logger('orderAttach');
		//logger($oldTimesheetId);
		//logger($newTimesheetId);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderMove(Order $order, $oldTimesheetId, $newTimesheetId) {
		logger('orderMove');
		//logger($oldTimesheetId);
		//logger($newTimesheetId);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderDoprun($orderId, $oldTimesheetId, $newTimesheetId) {
		logger('orderDoprun');
		//$order = Order::find($orderId);
		//logger($order);
		//logger($oldTimesheetId);
		//logger($newTimesheetId);
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderToConfirm($order, $status) {
		logger('orderToConfirm');
		//logger($order);
		//logger($status);
	}
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderConfirm(ConfirmedOrder $confirmedOrder) {
		logger('orderConfirm');
		//$order = Order::find($confirmedOrder?->order_id);
		//logger($order);
		//logger($confirmedOrder);
	}
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function ordersConfirm($rows) {
		logger('ordersConfirm');
		//$ordersIds = $rows->pluck('order_id')->toArray();
		//$orders = Order::find($ordersIds);
		//logger($rows->toArray());
		//logger($orders);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function ordersRemoveFromConfirmed(Order $order) {
		logger('orderRemoveFromConfirmed');
		//logger($order);
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orderDetach(Order $order, $status) {
		logger('orderDetach '.$status);
		//logger($status);
		//logger($order);
	}
	
	

	
	
	
	
	
}