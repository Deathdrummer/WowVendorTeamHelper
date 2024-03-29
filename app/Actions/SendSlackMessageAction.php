<?php namespace App\Actions;

use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use App\Models\Timesheet;
use App\Traits\Settingable;
use Illuminate\Support\Str;
use Ixudra\Curl\Facades\Curl;

class SendSlackMessageAction {
	use Settingable;
	
	/**
	* Отправить оповещение в Slack
	* @param array [webhook, order_id, message]
	* @return 
	*/
	public function __invoke($params = []) {
		$endpoint = $params['webhook'] ?? false;
		if (!$endpoint) return response()->json(false);
		
		//$timezones = $this->getSettings('timezones', 'id', 'timezone');
		
		$settings = $this->getSettings([[
			'setting'	=> 'timezones',
			'key'		=> 'id',
			'value'		=> 'timezone',
		], [
			'setting'	=> 'orders_types',
			'key'		=> 'id',
			'value'		=> 'title',
		]]);
		
		$timezones = $settings['timezones'] ?? [];
		$ordersTypes = $settings['orders_types'] ?? [];
		
		$orderData = Order::find($params['order_id']);
		$timesheetId = $params['timesheet_id'] ?? null;
		
		$message = $params['message'] ?? '';
		
		if (is_array($params['order_id'])) {
			$messText = '';
			$orderData->each(function($order) use(&$messText, $timezones, $ordersTypes, $timesheetId, $message) {
				$timesheet = Timesheet::find($timesheetId);
				$messText .= $this->buildMess([
					'order' 		=> $order,
					'timesheet' 	=> $timesheet,
					'message' 		=> $message,
					'timezones' 	=> $timezones,
					'ordersTypes' 	=> $ordersTypes,
				]);
				$messText .= "\n";
			});
		} else {
			$timesheet = Timesheet::find($timesheetId);
			$messText = $this->buildMess([
				'order' 		=> $orderData,
				'timesheet' 	=> $timesheet,
				'message' 		=> $message,
				'timezones' 	=> $timezones,
				'ordersTypes' 	=> $ordersTypes,
			]);
		}
		
		return $this->send([
			'endpoint' 	=> $endpoint,
			'message' 	=> trim($messText),
		]);
	}
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function buildMess($params = []) {
		if (!$params) return false;
		extract($params);
		
		$orderData = $order;
		
		$rawData = $orderData->raw_data ?? '---';;
		$timezone = $timezones[$orderData->timezone_id] ?? '---';
		$status = OrderStatus::fromValue($orderData->status)->key;
		$order = $orderData?->order ?? '---';
		$orderType = $ordersTypes[$orderData->order_type] ?? '---';
		$fraction = $orderData?->fraction ?? '---';
		$battleTag = $orderData?->battle_tag ?? '---';
		$price = $orderData?->price ?? '---';
		$serverName = $orderData?->server_name ?? '---';
		$link = $orderData?->link ?? '---';
		$dateOrig = $orderData?->date ? DdrDateTime::date($orderData?->date).' в '.DdrDateTime::time($orderData?->date) : '-';
		$dateMsc = $orderData?->date_msc ? DdrDateTime::date($orderData->date_msc).' в '.DdrDateTime::time($orderData->date_msc) : '-';
		$dateAdd = $orderData?->date_add ? DdrDateTime::date($orderData->date_add).' в '.DdrDateTime::time($orderData->date_add) : '-';
		$dateTs = $timesheet?->datetime ? DdrDateTime::date($timesheet?->datetime).' в '.DdrDateTime::time($timesheet?->datetime) : '-';
		
		$statuses = [
			'new'		=> 'новый',
			'wait'		=> 'ожидание',
			'cancel'	=> 'отменен',
			'ready'		=> 'готов',
			'doprun'	=> 'допран',
		];
		
		return Str::swap([
			'{{raw}}' 				=> $rawData,
			'{{timezone}}' 			=> $timezone,
			'{{status}}' 			=> $statuses[$status] ?? '-',
			'{{order}}' 			=> $order,
			'{{order_type}}'		=> $orderType,
			'{{fraction}}'			=> $fraction,
			'{{battle_tag}}'		=> $battleTag,
			'{{price}}' 			=> $price,
			'{{server_name}}'		=> $serverName,
			'{{link}}' 				=> $link,
			'{{date_orig}}' 		=> $dateOrig,
			'{{date_msc}}' 			=> $dateMsc,
			'{{date_add}}' 			=> $dateAdd,
			'{{date_timesheet}}'	=> $dateTs,
		], $message ?? '');
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param array $params ['endpoint', 'message']
	* @return 
	*/
	private function send($params = []) {
		extract($params);
		
		$resp = Curl::to($endpoint)
			->withData(['payload' => json_encode(["text" => $message])])
			->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
			->withContentType('application/json')
			//->returnResponseObject()
			->post();
		
		if ($resp == 'no_service') toLog('SendSlackMessageAction -> send сообщение не отправилось, что-то не так с вебхуком!');
			
		return !!($resp == 'ok');
	}
	
	
	
}

