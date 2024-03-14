<?php namespace App\Actions;

use Error;
use Ixudra\Curl\Facades\Curl;

class SendMessageToSlackAction {
	
	/**
	* Отправить сообщение в слак
	* @param integer $eventType Тип события
	* @param array $info информация
	* @return array
	*/
	public function __invoke($params = []) {
		extract($params);
		if (!$message || !$webhook) throw new Error('LogEventAction ошибка -> отсутствуют обязательные аргументы!');
		
		$payload['text'] = $message;
		
		if ($attachments) {
			$attData = [];
			foreach ($attachments as $attachment) {
				$attData[] = [
					'image_url' => $attachment,
					'fallback' => null, # не знаю что это, в слак никак не отображается
				];
			}
			$payload['attachments'] = $attData;
		}
		
		
		$resp = Curl::to($webhook)
			->withData(['payload' => json_encode($payload)])
			->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
			->withContentType('application/json')
			//->returnResponseObject()
			->post();
		
		
		if ($resp == 'no_service') {
			toLog('SendMessageToSlackAction -> сообщение не отправилось, что-то не так с вебхуком!');
			return false;
		} 
			
		return !!($resp == 'ok');
	}
}