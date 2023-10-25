<?php namespace App\Http\Controllers;

use App\Actions\SendSlackMessageAction;
use App\Events\SendMessageEvent;
use App\Models\Command;
use App\Models\Order;
use App\Models\Timesheet;
use App\Services\Business\OrderService;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
				
				$row['order_type'] = $orderService->setOrderType($row);
				
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
			'id'			=> $id,
			'order_id'		=> $orderId,
			'timesheet_id'	=> $timesheetId,
		] = $request->validate([
			'id'			=> 'required|numeric',
			'order_id'		=> 'required|numeric',
			'timesheet_id'	=> 'required|numeric',
		]);
		
		$notifyButtons = $this->getSettings('slack_notifies', 'id', null, 'id:'.$id);
		if (!$data = $notifyButtons[$id] ?? null) return response()->json(false);
		$response = null;
		$executed = RateLimiter::attempt(
			'send_message:'.$orderId,
			$perMinute = 1,
			function() use($data, $orderId, $timesheetId, &$response) {
				$response = $this->_sendMessage($data, $orderId, $timesheetId);
			},
			$decayRate = (int)($data['timeout'] ?? 0),
		);
		
		if (!$executed || !$response) return response()->json(false);
		
		$response['timeout'] = (int)($data['timeout'] ?? 0);
		
		return response()->json($response);
	}
	
	
	
	
	
	/** Отправить сообщение в Slack
	* @param 
	* @return bool
	*/
	private function _sendMessage($data, $orderId, $timesheetId):bool {
		$webhooks = explode("\n", $data['webhook']);
		if (!$webhooks) return false;
		
		$sendMessage = app()->make(SendSlackMessageAction::class);
		$timesheet = Timesheet::find($timesheetId);
		$commands = Command::all()->pluck('id', 'title')->toArray();
		
		foreach ($webhooks as $webhook) {
			$splitRow = preg_split('/\s+/', trim($webhook));
			$webhook = isset($splitRow[1]) ? $splitRow[1] : ($splitRow[0] ?? null);
			$command = isset($splitRow[1]) ? $commands[$splitRow[0]] : null;
			
			if (is_null($webhook) || (!is_null($command) && $timesheet?->command_id != $command) ) continue;
			
			return $sendMessage([
				'order_id' => $orderId,
				'webhook' => $webhook ?? null,
				'message' => $data['message'] ?? null,
			]);
		}
		
		return false;
	}

}