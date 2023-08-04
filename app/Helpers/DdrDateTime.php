<?php namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class DdrDateTime {
	
	/** Вывести дату в ЧПУ формате: 12 июля 2023 г.
	 * @param timestamp $timestamp
	 * @param array $shift, $locale, $isoFormat
	 * @return string
	 */
    public static function date($timestamp = null, $params = []):string {
		extract($params);
		
		$locale = $locale ?? App::currentLocale();
		
		$defaultFormat = match($locale) {
			'ru'		=> 'D MMMM YYYY г.',
			'en'		=> ['MMMM DD YYYY', 'Do MMMM'],
			default		=> 'D MMMM YYYY',
		};
		
		$format = match(true) {
			!isset($format) 	=> $defaultFormat,
			is_array($format) 	=> $format[$locale] ?? $defaultFormat,
			is_string($format) 	=> $format ?? $defaultFormat,
			default				=> $defaultFormat,
		};
		
		$shift = match(true) {
			$shift ?? false				=> config('app.shift_hours', 0),
			is_numeric($shift ?? null)	=> (int)$shift,
			default						=> null,
		};
		
		return self::buildCarbon([
			'timestamp' => $timestamp,
			'isoFormat'	=> $format,
			'shift' 	=> $shift,
			'locale' 	=> $locale,
		]);
    }
	
	
	
	/** Вывести время
	 * @param timestamp $timestamp
	 * @param array $shift, $locale, $format
	 * @return string
	 */
	 public static function time($timestamp = null, $params = []):string {
		extract($params);
		
		$locale = $locale ?? App::currentLocale();
		
		$defaultFormat = match($locale) {
			'ru'		=> 'H:i',
			'en'		=> 'h:i a',
			default		=> 'H:i',
		};
		
		$format = match(true) {
			!isset($format) 	=> $defaultFormat,
			is_array($format) 	=> $format[$locale] ?? $defaultFormat,
			is_string($format) 	=> $format ?? $defaultFormat,
			default				=> $defaultFormat,
		};
		
		$shift = match(true) {
			$shift ?? false				=> config('app.shift_hours', 0),
			is_numeric($shift ?? null)	=> (int)$shift,
			default						=> null,
		};
		
		return self::buildCarbon([
			'timestamp' => $timestamp,
			'format' 	=> $format,
			'shift' 	=> $shift,
			'locale' 	=> $locale,
		]);
    }
	
	
	
	
	
	
	/** Сформировать объект Carbon из строк даты и/или времени
	 * @param string $date
	 * @param string|array $time
	 * @param array $shift
	 * @return Carbon
	 */
	public static function buildTimestamp($date = null, $time = null, $params = []):Carbon {
		if (is_array($time) && empty($params)) {
			$params = $time;
			$time = null;
		}
		
		extract($params);
		
		$timestamp = match(true) {
			isset($time) 	=> $date.' '.$time,
			!isset($time) 	=> $date,
			default			=> null,
		};
		
		$shift = match(true) {
			$shift ?? false				=> (-1 * config('app.shift_hours', 0)), // так как мы ориентируемся на UTC - от указанной даты мы наоборот, отномаем смещение
			is_numeric($shift ?? null)	=> (int)$shift,
			default						=> null,
		};
		
		return self::buildCarbon([
			'timestamp' => $timestamp,
			'shift' 	=> $shift,
			'locale' 	=> $locale ?? App::currentLocale(),
		]);
	}
	
	
	
	
	
	
	
	/** Сместить время объекта Carbon
	 * @param Carbon|string $timestamp
	 * @param integer $shift - UTC, TZ, [значение]
	 * @return Carbon
	 */
	public static function shift($timestamp = null, $shift = null):Carbon {
		$shift = match(true) {
			($shift ?? null) == 'UTC'		=> (-1 * config('app.shift_hours', 0)), // так как мы ориентируемся на UTC - от указанной даты мы наоборот, отномаем смещение
			($shift ?? null) == 'TZ'		=> config('app.shift_hours', 0),
			is_numeric($shift ?? null)	=> (int)$shift,
			default						=> null,
		};
		
		return self::buildCarbon([
			'timestamp' => $timestamp,
			'shift' 	=> $shift,
		]);
	}
	
	
	
	
	
	
	
	
	//-----------------------------------------------
	
	
	
	/**
	 * @param array $timestamp, $shift, $locale, $format, $isoFormat
	 * @return Carbon|string
	 */
	static private function buildCarbon($params = []):Carbon|string {
		extract($params);
		
		$carbon = match(true) {
			!isset($timestamp)		=> Carbon::now(),
			is_string($timestamp)	=> Carbon::parse($timestamp),
			is_object($timestamp)	=> Carbon::create($timestamp),
			default					=> Carbon::now(),
		};
		
		if (isset($shift)) $carbon = $carbon->addHours($shift);
		
		if (isset($locale)) $carbon = $carbon->locale($locale);
		
		if (isset($format)) $carbon = $carbon->format($format);
		elseif (isset($isoFormat)) {
			if (is_array($isoFormat)) $carbon = $carbon->isoFormat(...$isoFormat);
			else $carbon = $carbon->isoFormat($isoFormat);
		} 
		
		return $carbon;
	}
	
	
}