<?php namespace App\Traits;

use App\Services\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait Settingable {
	
	
	private $settingsData = [];

	
	/** Добавить данные из настроек в глобальную переменную data
	 * чтобы получить сразу несколько данных из настроек - можно передать любое количество массовов: [
	 *		'setting'	=> 'contract-types',
	 *		'key'		=> 'id',
	 *		'value'		=> 'title',
	 *		'filter'	=> 'id:3'
	 * ] 
	 * @param mixed $setting
	 * @param string $key
	 * @param string $value
	 * @param string $filter
	 * @return void
	 */
	private function addSettingToGlobalData($setting = null, ?string $key = null, ?string $value = null, $filter = null) {
		if (!isset($this->data)) throw new \Exception('addSettingToGlobalData не объявлена переменная data');
		$this->_getSettings($setting, $key, $value, $filter);
		$this->data = array_replace_recursive($this->data, $this->settingsData);
	}
	
	
	
	/** Получить данные из настроек и вернуть их 
	 * чтобы получить сразу несколько данных из настроек - можно передать любое количество массовов: [
	 *		'setting'	=> 'contract-types',
	 *		'key'		=> 'id',
	 *		'value'		=> 'title',
	 *		'filter'	=> 'id:3'
	 * ] 
	 * @param mixed $setting
	 * @param string $key
	 * @param string $value
	 * @param string $filter
	 * @return mixed
	 */
	public function getSettings($setting = null, ?string $key = null, ?string $value = null, $filter = null):mixed {
		
		//trim($setting.'-'.$key.'-'.$value.'-'.(is_array($filter) ? key($filter).':'.current($filter) : $filter), '-')
		
		return Cache::remember('getSettings-'.argsToStr(func_get_args()), 3, function () use($setting, $key, $value, $filter) {
			//logger(trim($setting.'-'.$key.'-'.$value.'-'.$filter, '-'));
			$this->_getSettings($setting, $key, $value, $filter);
			
			if (isset($key)) {
				$k = splitString($setting, ':');
				$key = array_pop($k);
				return isset($this->settingsData[$key]) ? $this->settingsData[$key] : $this->settingsData;
			} 
			
			if (!is_array($setting)) {
				if (!Str::contains($setting, ':')) return isset($this->settingsData[$setting]) ? $this->settingsData[$setting] : $this->settingsData;
				
				$k = splitString($setting, ':');
				$key = array_pop($k);
				return isset($this->settingsData[$key]) ? $this->settingsData[$key] : $this->settingsData;
			}
			
			return $this->settingsData;
		});
	}
	
	
	
	/** Получить данные из настроек и вернуть их (статическая функция)
	 * чтобы получить сразу несколько данных из настроек - можно передать любое количество массовов: [
	 *		'setting'	=> 'contract-types',
	 *		'key'		=> 'id',
	 *		'value'		=> 'title',
	 *		'filter'	=> 'id:3'
	 * ] 
	 * @param mixed $setting
	 * @param string $key
	 * @param string $value
	 * @param string $filter
	 * @return mixed
	 */
	public static function getSettingsStatic(...$params) {
		return (new class { use Settingable; })->getSettings(...$params);
	}
	
	
	
	/**
	 * @param 
	 * @return Collection|null
	 */
	public function getSettingsCollect(...$params):Collection|null {
		return collect($this->getSettings(...$params));
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function _getSettings($setting = null, ?string $key = null, ?string $value = null, $filter = null) {
		if (!$setting) throw new \Exception('_getSettings не переданы параметры');
		
		$settingsService = app()->make(Settings::class);
		
		if (is_array($setting)) {
			$setting = collect($setting);
			//$settings = $setting->pluck('setting');
			
			$keyedSettings = $setting->mapWithKeys(function ($item) {
				[$s, $r] = $this->_parseSettingString($item['setting']);
				return [$s => [
					'setting'	=> $s,
					'rename'	=> $r,
					'key'		=> $item['key'] ?? null,
					'value'		=> $item['value'] ?? null,
					'filter'	=> $item['filter'] ?? null,
				]];
			});
			
			$ranameMap = $keyedSettings->mapWithKeys(function ($item) {
				return [$item['setting'] => $item['rename']];
			});
			
			
			if (!$dataFromSettings = $settingsService->getMany($keyedSettings->keys()->all())) {
				foreach ($ranameMap->all() as $set => $rename) $this->settingsData[$rename ?? $set] = false;
				return false;
			}
			
			
			$dataFromSettings->each(function ($settingValue, $settingKey) use($keyedSettings) {
				$settingValue = collect($settingValue);
				[
					'setting' 	=> $setting,
					'rename' 	=> $rename,
					'key' 		=> $key,
					'value'		=> $val,
					'filter' 	=> $filter
				] = $keyedSettings->get($settingKey);
				
				if ($filter) $settingValue = $this->_setFilter($settingValue, $filter);
				
				$settingValue->sortKeys(SORT_NUMERIC);
				
				$settingValue = $this->_restructureData($settingValue, $key, $val);
				
				$this->settingsData[$rename ?? $setting] = match (gettype($settingValue)) {
					'object'	=> $settingValue->toArray(),
					default 	=> $settingValue
				};
				
			});
			
		} else {
			
			[$sName, $sRename] = $this->_parseSettingString($setting);
			
			if (!$fromSettingsData = $settingsService->get($sName)) {
				$this->settingsData[$sRename ?? $sName] = false;
				return false;
			} 
			
			$isCollection = $this->_isCollection($fromSettingsData);
			
			if ($isCollection && $filter) $fromSettingsData = $this->_setFilter($fromSettingsData, $filter);
			
			if (!$fromSettingsData) {
				$this->settingsData[$sRename ?? $sName] = false;
				return false;
			}
			
			
			
			if ($isCollection) {
				$fromSettingsData->sortKeys(SORT_NUMERIC);
				if ($key || $value) $fromSettingsData = $this->_restructureData($fromSettingsData, $key, $value);
			} 
			
			
			$this->settingsData[$sRename ?? $sName] = match ($isCollection) {
				true	=> $fromSettingsData->toArray(),
				default	=> $fromSettingsData,
			};
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/** Является ли данные объектом коллекции
	 * @param 
	 * @return 
	 */
	private function _isCollection($data = null) {
		return $data instanceof Collection;
	}
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _setFilter($data, $filter) {
		if (is_array($filter)) {
			$filterKey = key($filter) ?? null;
			$filterValue = current($filter) ?? null;
		} else {
			$f = splitString($filter, ':');
			$filterKey = $f[0] ?? null;
			$filterValue = $f[1] ?? null;
		}
		
		$data = $data->filter(function ($value) use($filterKey, $filterValue) {
			if (is_array($value) && $value[$filterKey] == $filterValue) return true;
			elseif (!is_array($value) && $value == $filterKey) return true;
			return false;
		});
		return $data;
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function _restructureData($data, $key, $value) {
		if ($key && !$value) {
			if (Str::contains($key , ':')) {
				[$key, $comand] = splitString($key, ':');
			}
			if (isset($comand) && $comand == 'many') {
				$data = $data->mapToGroups(function ($item) use($key) {
					return [$item[$key] ?? null => $item ?? null];
				});
			
			} elseif (isset($comand) && $comand == 'single') {
				$data = $data[0];
			} else {
				$data = $data->keyBy($key);
			}
			
		} elseif (!$key && $value) {
			$data = $data->mapWithKeys(function ($item, $k) use($value) {
				return [$k ?? null => $item[$value] ?? null];
			});
			
		}  elseif ($key && $value) {
			$data = $data->mapWithKeys(function ($item) use($key, $value) {
				return [$item[$key] ?? null => $item[$value] ?? null];
			});
		}
		return $data;
	}
	
	
	
	
	/**
	 * @param ?string  $setting
	 * @return array
	 */
	private function _parseSettingString(?string $setting = null): array {
		if (!$setting) return false;
		if (Str::contains($setting, ':')) {
			$s = splitString($setting, ':');
			$rename = array_pop($s);
			$setting = implode(':', $s);
			return [$setting, $rename];
		}
		return [$setting, null];
	}
	
	
	
	
	
	
	
	
}