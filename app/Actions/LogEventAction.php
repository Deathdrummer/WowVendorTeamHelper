<?php namespace App\Actions;

use App\Helpers\DdrDateTime;
use App\Models\EventLog;

class LogEventAction {
	
	/**
	* Отправить событие в логи
	* @param integer $eventType Тип события
	* @param array $info информация
	* @return array
	*/
	public function __invoke($eventType = null, $info = null) {
		if (!$eventType) return false;
		
		$guard = getGuard();
		
		$selfId = auth($guard)->user()->id;
		
		$userType = match($guard) {
			'site'	=> 1,
			'admin'	=> 2,
			default	=> 1,
		};
		
		return EventLog::create([
			'from_id'		=> $selfId,
			'user_type'		=> $userType,
			'event_type'	=> $eventType,
			'info'			=> $info,
			'datetime'		=> DdrDateTime::now(),
		]);
	}
}