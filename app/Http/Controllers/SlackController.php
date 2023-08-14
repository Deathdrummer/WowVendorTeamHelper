<?php namespace App\Http\Controllers;

use App\Events\SendMessageEvent;
use App\Models\Order;
use App\Services\Business\OrderService;
use App\Traits\Settingable;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

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
		
		$dateAdd = substr($event['ts'], 0, strpos($event['ts'], '.'));
		
		$data = $orderService->parse($message);
		
		if ($data) {
			$dataToRows = [];
			
			foreach ($data as $row) {
				$row['date_add'] = $dateAdd;
				
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
		
		//logger('rtyrtytryrty');
		
		
		$endpoint = $request->input('webhook', false);
		if (!$endpoint) return false;
		$response = Curl::to($endpoint)
			->withData(['payload' => json_encode(["text" => $request->input('text', 'Это сообщение отправено из сервиса')])])
			->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
			->withContentType('application/json')
			->returnResponseObject()
			->post();
		
		return response()->json($response);
	}
	

}