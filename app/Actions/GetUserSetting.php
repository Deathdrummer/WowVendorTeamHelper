<?php namespace App\Actions;

class GetUserSetting {
	/**
	* 
	* @param 
	* @return 
	*/
	public function __invoke($key = null) {
		
		$userSettings = auth('site')->user()->settings;
		
		$current = data_get($userSettings, $key);
		
		return $current;
	}
}