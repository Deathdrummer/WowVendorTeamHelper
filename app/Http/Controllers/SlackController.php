<?php namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Events\SendMessageEvent;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use App\Services\Business\OrderService;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Str;

class SlackController extends Controller {
	use Settingable;
    
	/** Прослушки новых сообщений (Если не работает - значит ngrok заменить ссылку)
	 * @param 
	 * @return 
	 */
	public function incomingOrder(Request $request, OrderService $orderService) {
		$event = $request->input('event'); // содержит в себе всю информацию
		
		//$message = isset($event['subtype']) ? ($event['message'][0]['text'] ?? null) : $event['text'] ?? null; //----- для ручного теста
		$message = isset($event['blocks']) ? ($event['blocks'][2]['text']['text'] ?? null) : null; // Новая версия
		
		$dateAdd = $event['ts'] ? substr($event['ts'], 0, strpos($event['ts'], '.')) : Carbon::now();
		
		$data = $orderService->parse($message);
		
		if ($data) {
			$dataToRows = [];
			
			foreach ($data as $row) {
				$row['date_add'] = Carbon::createFromTimestamp($dateAdd)->toDateTimeString();
				
				['id' => $id, 'date_msc' => $dateMsc] = Order::create($row);
				
				$row['id'] = $id;
				$row['date_msc'] = $dateMsc;
				
				$dataToRows[] = $row;
			}
			
			broadcast(new SendMessageEvent($dataToRows));
		}
		
		return response()->json(['challenge' => $request->input('challenge')])->withHeaders(['Content-Type' => 'text/plain',]);
	}
	
	
	
	
	
	
	
	/** Отправить сообщение в Slack
	 * @param 
	 * @return 
	 */
	public function send_message(Request $request) {
		[
			'id'		=> $id,
			'order_id'	=> $orderId,
		] = $request->validate([
			'id'		=> 'required|numeric',
			'order_id'	=> 'required|numeric',
		]);
		
		$notifyButtons = $this->getSettings('slack_notifies', 'id', null, 'id:'.$id);
		
		if (!$data = $notifyButtons[$id] ?? null) return response()->json(false);
		

		$endpoint = $data['webhook'] ?? false;
		if (!$endpoint) return response()->json(false);
		
		$timezones = $this->getSettings('timezones', 'id', 'timezone');
		
		$orderData = Order::find($orderId);
		
		
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

		$message = Str::swap([
			'{{raw}}' 			=> $rawData,
			'{{timezone}}' 		=> $timezone,
			'{{status}}' 		=> $status,
			'{{order}}' 		=> $order,
			'{{price}}' 		=> $price,
			'{{server_name}}'	=> $serverName,
			'{{link}}' 			=> $link,
			'{{date_orig}}' 	=> $dateOrig,
			'{{date_msc}}' 		=> $dateMsc,
			'{{date_add}}' 		=> $dateAdd,
		], $data['message'] ?? '');
		
		$response = Curl::to($endpoint)
			->withData(['payload' => json_encode(["text" => $message])])
			->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
			->withContentType('application/json')
			->returnResponseObject()
			->post();
		
		return response()->json($response);
	}
	

}