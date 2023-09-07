<?php namespace App\Actions;

use App\Helpers\DdrDateTime;
use App\Models\EventLoger;

class LogEventAction {
	
	/**
	* Отправить событие в логи
	* @param integer $eventType Тип события
	* @return array
	*/
	public function __invoke($eventType = null):array {
		if (!$eventType) return false;
		
		$guard = getGuard();
		
		$selfId = auth($guard)->user()->id;
		
		$userType = match($guard) {
			'site'	=> 1,
			'admin'	=> 2,
			default	=> 1,
		};
		
		return EventLoger::create([
			'from_id'		=> $selfId,
			'user_type'		=> $userType,
			'event_type'	=> $eventType,
			'datetime'		=> DdrDateTime::now(),
		])->toArray();
	}
}