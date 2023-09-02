<?php namespace App\Actions;

use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use App\Traits\Settingable;
use Illuminate\Http\Response;
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
		
		$timezones = $this->getSettings('timezones', 'id', 'timezone');
		
		$orderData = Order::find($params['order_id']);
		
		$rawData = $orderData['raw_data'];
		$timezone = $timezones[$orderData->timezone_id];
		$status = OrderStatus::fromValue($orderData->status)->key;
		$order = $orderData['order'] ?? '---';
		$price = $orderData['price'] ?? '---';
		$serverName = $orderData['server_name'] ?? '---';
		$link = $orderData['link'] ?? '---';
		$dateOrig = DdrDateTime::date($orderData->date).' в '.DdrDateTime::time($orderData->date);
		$dateMsc = DdrDateTime::date($orderData->date_msc).' в '.DdrDateTime::time($orderData->date_msc);
		$dateAdd = DdrDateTime::date($orderData->date_add).' в '.DdrDateTime::time($orderData->date_add);
		
		
		$statuses = [
			'new'		=> 'новый',
			'wait'		=> 'ожидание',
			'cancel'	=> 'отменен',
			'ready'		=> 'готов',
			'doprun'	=> 'допран',
		];




		$message = Str::swap([
			'{{raw}}' 			=> $rawData,
			'{{timezone}}' 		=> $timezone,
			'{{status}}' 		=> $statuses[$status] ?? '-',
			'{{order}}' 		=> $order,
			'{{price}}' 		=> $price,
			'{{server_name}}'	=> $serverName,
			'{{link}}' 			=> $link,
			'{{date_orig}}' 	=> $dateOrig,
			'{{date_msc}}' 		=> $dateMsc,
			'{{date_add}}' 		=> $dateAdd,
		], $params['message'] ?? '');
		
		return Curl::to($endpoint)
			->withData(['payload' => json_encode(["text" => $message])])
			->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
			->withContentType('application/json')
			->returnResponseObject()
			->post();
	}
}

