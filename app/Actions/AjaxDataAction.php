<?php namespace App\Actions;

use Illuminate\Support\Arr;

class AjaxDataAction {
	
	/**
	* Работа с AJAX данными
	* @param mixed  $data входящие данные
	* @param array  $params параметры
	* @return mixed
	*/
	public function __invoke($data, $params = []):mixed {
		extract($params);
		
		// 'setting' 	
		// 'val' 		
		// 'type'  // arr single
		// remove
		
		$value = bringTypes($value);
		
		$data = $data ?: ($type == 'arr' ? [] : null);
		
		
		
		
		if ($type == 'arr') {
			$currentData = $data ? data_get($data, $setting) : [];
			
			if ($remove) {
				if (($searched = array_search($value, $currentData)) === false) return $data ?: null;
				unset($currentData[$searched]);
				$currentData = array_values($currentData) ?: false;
			} else {
				$currentData[] = $value;
			}
			
			if ($currentData) data_set($data, $setting, $currentData);
			else Arr::forget($data, $setting);
			
		} elseif ($type == 'single') {
			if ($remove) {
				Arr::forget($data, $setting);
			} else {
				if ($value) data_set($data, $setting, $value);
				else Arr::forget($data, $setting);
			}
		}

		return $data ?: null;
	}
		
}