<?php namespace App\Actions;

class GetUserSetting {
	/**
	* 
	* @param string|array $key  ключ массива данных
	* @return mixed
	*/
	public function __invoke($key = null):mixed {
		
		$userSettings = auth('site')->user()->settings;
		
		if (!is_array($key)) return data_get($userSettings, $key);
		
		$data = [];
		foreach ($key as $k) {
			$data[$k] = data_get($userSettings, $k);
		}
		
		return $data;
	}
}