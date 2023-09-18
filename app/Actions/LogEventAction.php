<?php namespace App\Actions;

use App\Helpers\DdrDateTime;
use App\Models\EventLog;
use Error;

class LogEventAction {
	
	/**
	* Отправить событие в логи
	* @param integer $eventType Тип события
	* @param array $info информация
	* @return array
	*/
	public function __invoke($group = null, $eventType = null, $info = null) {
		if (!$group || !$eventType) throw new Error('LogEventAction ошибка -> отсутствуют обязательные аргументы!');
		
		$guard = getGuard();
		
		$selfId = auth($guard)->user()->id;
		
		$userType = match($guard) {
			'site'	=> 1,
			'admin'	=> 2,
			default	=> 1,
		};
		
		$sortCounter = 0;
		foreach ($info as $field => $data) {
			$info[$field]['sort'] = $sortCounter++;
		}
		
		return EventLog::create([
			'from_id'		=> $selfId,
			'user_type'		=> $userType,
			'event_type'	=> $eventType,
			'group'			=> $group,
			'info'			=> $info,
			'datetime'		=> DdrDateTime::now(),
		]);
	}
}