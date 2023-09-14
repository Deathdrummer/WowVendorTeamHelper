<?php namespace App\Models\Traits;

use App\Helpers\DdrDateTime;
use Carbon\Carbon;
use Error;

trait HasEvents {
	
	/**
	* Проверяет, изменилась ли дата
	* @param $dateOrig оригинальная дата
	* @param $dateChanged измененная дата
	* @return bool
	*/
	public static function isDateChanged($dateOrig, $dateChanged):bool {
		if (!$dateOrig || !$dateChanged) return false;
		$dChanged = DdrDateTime::shift($dateChanged, 'TZ')->toDateTimeString();
		$dOrig = $dateOrig->toDateTimeString();
		return $dOrig !== $dChanged;
	}
	
	
	
	
	
	/**
	* 
	* @param $model - модель
	* @param array $mapValues - данные для подстановки [название поля => массив данных (в качестве ключа значение поля)]
	* @param array $datetimeFields - поля даты
	* @return 
	*/
	public static function buildFields($model = null, ?array $mapValues = [], ?array $datetimeFields = []) {
		if (is_null($model)) throw new Error('Ошибка -> трейт: HasEvents, функция: getChangedFields - не передана модель!');
		
		$originalData = $model?->getRawOriginal() ?? null; // данные ДО обновления  БЕЗ мутирования. getOriginal - получить ДО обновления С мутаторами
		$changedData = $model?->getAttributes() ?? null; // измененные данные БЕЗ мутирования
		
		if (!$changedData) return false;
		
		$buildedFields = [];
		foreach ($originalData as $field => $value) {
			$buildedFields[$field]['data'] = in_array($field, $datetimeFields) ? DdrdateTime::buildTimestamp($value) : self::mapField($field, $value, $mapValues);
			if (in_array($field, $datetimeFields) && !self::isDateChanged(DdrdateTime::buildTimestamp($value), $changedData[$field])) continue;
			
			if ($value != ($changed = bringTypes($changedData[$field]))) {
				$buildedFields[$field]['updated'] = in_array($field, $datetimeFields) ? $changedData[$field] : self::mapField($field, $changed, $mapValues);
			}
		}
		
		return function($field = null, $cb = null, $title = null) use($buildedFields) {
			if ($cb && !is_callable($cb)) {
				$title = $cb;
				$cb = null;
			}
			
			if (!$cb) {
				if ($title) $buildedFields[$field]['title'] = $title;
				return $buildedFields[$field];
			}
			
			$cbData = $cb($buildedFields[$field]['data'] ?? null, $buildedFields[$field]['updated'] ?? null);
			if ($title && is_array($cbData)) $cbData['title'] = $title;
			return $cbData;
		};
	}
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private static function mapField($field, $value, $mapValues) {
		if (!in_array($field, array_keys($mapValues))) return $value;
		return $mapValues[$field][$value] ?? null;
	}
	
	
}