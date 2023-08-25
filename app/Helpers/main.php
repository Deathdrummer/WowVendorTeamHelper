<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Encoder\IdnAddressEncoder;


if (! function_exists('ddr_data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function ddr_data_set(&$target, $key, $value, $overwrite = true) {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    ddr_data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
				if (count($segments) == 1 && reset($segments) === '') {
					if (!in_array($value, $target[$segment] ?? [])) $target[$segment][] = $value;
				} else {
					if (! Arr::exists($target, $segment)) {
						$target[$segment] = [];
					}

					ddr_data_set($target[$segment], $segments, $value, $overwrite);
				}	
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                ddr_data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                ddr_data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}




if (! function_exists('ddr_data_forget')) {
    /**
     * Remove / unset an item from an array or object using "dot" notation.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @return mixed
     */
    function ddr_data_forget(&$target, $key) {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*' && Arr::accessible($target)) {
            if ($segments) {
                foreach ($target as &$inner) {
                    ddr_data_forget($inner, $segments);
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments && Arr::exists($target, $segment)) {
                ddr_data_forget($target[$segment], $segments);
            } else {
				$isNumArr = !Arr::isAssoc($target);
                Arr::forget($target, $segment);
				if ($isNumArr) $target = array_values($target);
            }
        } elseif (is_object($target)) {
            if ($segments && isset($target->{$segment})) {
                ddr_data_forget($target->{$segment}, $segments);
            } elseif (isset($target->{$segment})) {
                unset($target->{$segment});
            }
        }

        return $target;
    }
}





if (! function_exists('bringTypes')) {
	/**
	 * Приводит типы данных элементов массива (стар. bringTypes)
	 * @param mixed $inpData 
	 * @return mixed
	*/
	function bringTypes($inpData = false):mixed {
		if(empty($inpData)) return false;
		if (is_array($inpData)) {
			$resData = [];
			foreach($inpData as $key => $val) {
				if(is_string($val)) $resData[$key] = trim($val);
				if(!is_array($val)) {
					if((is_bool($val) && $val === false) || $val === 'false' || $val === 'FALSE') $resData[$key] = false;
					elseif((is_bool($val) && $val === true) || $val === 'true' || $val === 'TRUE') $resData[$key] = true;
					elseif(is_null($val) || $val === 'null' || $val === 'NULL' || $val === null || $val === NULL || $val === '' || preg_match('/^\s+$/', $val)) $resData[$key] = null;
					elseif(is_float($val) || (preg_match('/^-?\d+\.\d+$/', $val) && substr($val, -1) != '0')) $resData[$key] = (float)$val;
					elseif(preg_match('/^-?\d+\.\d+$/', $val) && substr($val, -1) == '0') $resData[$key] = (string)$val;
					elseif(is_int($val) || preg_match('/^-?\d+$/', $val)) $resData[$key] = (int)$val;
					else $resData[$key] = (string)$val;
				} 
				else $resData[$key] = arrBringTypes($val);
			}
		} else {
			if((is_bool($inpData) && $inpData === false) || $inpData === 'false' || $inpData === 'FALSE') $resData = false;
			elseif((is_bool($inpData) && $inpData === true) || $inpData === 'true' || $inpData === 'TRUE') $resData = true;
			elseif(is_null($inpData) || $inpData === 'null' || $inpData === 'NULL' || $inpData === null || $inpData === NULL || $inpData === '' || preg_match('/^\s+$/', $inpData)) $resData = null;
			elseif(is_float($inpData) || (preg_match('/^-?\d+\.\d+$/', $inpData) && substr($inpData, -1) != '0')) $resData = (float)$inpData;
			elseif(preg_match('/^-?\d+\.\d+$/', $inpData) && substr($inpData, -1) == '0') $resData = (string)$inpData;
			elseif(is_int($inpData) || preg_match('/^-?\d+$/', $inpData)) $resData = (int)$inpData;
			else $resData = (string)$inpData;
		}
		return $resData;
	}
}











if (! function_exists('translit')) {
    /**
     * @param string  $value
     * @param bool  $slug
     * @param bool  $glue
     * @return string
     */
    function translit(?string $value = null, bool $slug = false, ?string $glue = '-') {
        if (!$value) return false;
		$converter = array(
			'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
			'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
			'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
			'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
			'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
			'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
			'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
	
			'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
			'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
			'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
			'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
			'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
			'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
			'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
		);
 
		$value = strtr($value, $converter);
		
		if ($slug) return Str::slug($value, $glue);
		return $value;
    }
}




if (! function_exists('translitSlug')) {
    /**
     * @param string  $value
     * @param string  $glue
     * @return string
     */
    function translitSlug(?string $value = null, ?string $glue = '-') {
        if (!$value) return false;
		return translit($value, true, $glue);
    }
}





if (! function_exists('getGuard')) {
    /**
     * @param array  $params
     * @return string
     */
    function getGuard(?array $params = null):string {
        $origin = request()->server('HTTP_ORIGIN') ?? request()->server('REQUEST_SCHEME').'://'.request()->server('SERVER_NAME');
		$fullPath = request()->server('HTTP_REFERER');
		$replaced = Str::replace($origin, '', $fullPath);
		
		if (!$params) return Str::is('/admin/*', $replaced) ? 'admin' : 'site';
		
		foreach ($params as $path => $guard) {
			if (Str::is($path, $replaced)) return $guard;
		}
    }
}










if (! function_exists('arrTakeItem')) {
	/**
	 * Извлекает элемент из массива, сокращая сам массив
	 * @param array $arr массив
	 * @param ключ массива
	 * @param искать в значениях
	 * @param также будут проверяться типы
	 * @return 
	*/
	function arrTakeItem(&$arr = false, $itemKeyOrVal = false, $isValue = false, $strict = false) {
		if (!$arr || !$itemKeyOrVal) return false;
		if ($isValue) {
			if (($key = array_search($itemKeyOrVal, $arr, $strict)) === false) return false;
			$takeItem = $arr[$key];
			unset($arr[$key]);
			return $takeItem;
		} else {
			if (!array_key_exists($itemKeyOrVal, $arr)) return false;
			$takeItem = $arr[$itemKeyOrVal];
			unset($arr[$itemKeyOrVal]);
			return $takeItem;
		} 
		return false;
	}
} 



if (! function_exists('splitString')) {
	/**
	 * Разбивает строку по заданному разделителя
	 * @param string $str строка
	 * @param array $separator разделитель
	 * @param array $strict строгий режим
	 * @return array|null
	*/
	function splitString(?string $str = null, ?string $separator = ',', $strict = false): array|null {
		if (is_null($str)) return null;
		$res = preg_split('/\s*\\'.$separator.'\s*/', $str);
		if (!$strict) return $res ?: null;
		foreach ($res as $k => $item) {
			if (is_numeric($item)) $res[$k] = strpos($item, '.') ? (float)$item : (int)$item;
			elseif ($item == 'null' || $item == 'NULL') $res[$k] = null;
			elseif ($item == 'false' || $item == 'FALSE') $res[$k] = false;
			elseif ($item == 'true' || $item == 'TRUE') $res[$k] = true;
		}
		return $res ?: null;
	}
}






if (! function_exists('pregSplit')) {
	/**
	 * Разбивает строку по разделителям: пробел , ; |
	 * @param array|null $arr массив
	 * @param array $separator разделитель
	 * @return array|null
	*/
	function pregSplit(?string $str = null): array|null {
		if (is_null($str)) return null;
		return preg_split('/\s*[,|]\s*|\s*[;]\s*|\s+/', $str) ?: null;
	}
}





if (!function_exists('isJson')) {
    /**
     * Является ли формат строки JSON
     * @param строка
     * @return bool
    */
    function isJson($string) {
        if (is_array($string) || !is_string($string) || is_numeric($string) || is_integer($string) || is_bool($string)) return false;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}












if (!function_exists('arrGetIndexFromField')) {
	/**
	 * Возвращает индекс элеменa массива по указанному значению указанного поля элеменa массива (стар. getIndexFromFieldValue)
	 * @param массив
	 * @param поле
	 * @param значение
	 * @return индекс
	*/
	function arrGetIndexFromField($array = [], $field = null, $value = null) {
		if(is_null($array) || is_null($field) || is_null($value)) return false;
		$res = array_filter($array, function($val, $key) use($field, $value) {
			return (isset($val[$field]) && $val[$field] == $value);
		}, ARRAY_FILTER_USE_BOTH);
		
		if ($res && count($res) > 1) {
			$keys = [];
			while ($item = current($res)) {
				$keys[] = key($res);
				next($res);
			}
			return $keys;
		} elseif ($res && count($res) == 1) {
			return key($res);
		} else {
			return false;
		}
	}
}














//--------------------------------------------




if (! function_exists('getActionFuncName')) {
	/**
	 * Извлекает элемент из массива, сокращая сам массив
	 * @param array $arr массив
	 * @param ...$addict  дополнительные параметры
	 * @return string|null
	*/
	function getActionFuncName(?string $actionString = null) {
		if (!$actionString) {
			echo null;
			return false;
		} 
		
		$actData = explode(':', $actionString);
		echo array_shift($actData) ?? null;
	}
}






if (! function_exists('buildAction')) {
	/**
	 * Извлекает элемент из массива, сокращая сам массив
	 * @param array $arr массив
	 * @param ...$addict  дополнительные параметры
	 * @return string|null
	*/
	function buildAction(?string $actionString = null, ...$addict) {
		if (!$actionString) {
			echo null;
			return false;
		} 
		
		$actData = explode(':', $actionString);
		$action = array_shift($actData) ?? null;
		$params = implode(':', $actData) ?? null;
		
		$paramsStrData = [];
		
		if ($addict) {
			foreach ($addict as $ad) {
				$ad = trim($ad);
				if ($ad === '') $ad = 'null';
				$paramsStrData[] = (is_numeric($ad) || in_array($ad, ['null', 'false', 'true'])) ? $ad : "'".$ad."'";
			}
		}
		
		if (isset($params) && ($splitParams = splitString($params, ','))) {
			foreach ($splitParams as $param) {
				$param = trim($param);
				if ($param === '') $param = 'null';
				$paramsStrData[] = (is_numeric($param) || in_array($param, ['null', 'false', 'true'])) ? $param : "'".$param."'";
			}
		}
		
		if ($paramsStrData) $onclick = '$.'.$action.'(this, '.implode(', ', $paramsStrData).')';
		else $onclick = '$.'.$action.'(this)';
		
		echo 'onclick="'.$onclick.'"';
	}
}








if (! function_exists('buildActionParams')) {
	/**
	 * Извлекает элемент из массива, сокращая сам массив
	 * @param array $arr массив
	 * @return string|null
	*/
	function buildActionParams(?string $actionString = null) {
		if (!$actionString) {
			echo null;
			return false;
		} 
		
		$actData = explode(':', $actionString);
		$action = array_shift($actData) ?? null;
		$params = implode(':', $actData) ?? null;
		
		$paramsStrData = [];
		if (isset($params) && ($splitParams = splitString($params, ','))) {
			foreach ($splitParams as $param) {
				$param = trim($param);
				if ($param === '') $param = 'null';
				$paramsStrData[] = (is_numeric($param) || in_array($param, ['null', 'false', 'true'])) ? $param : "'".$param."'";
			}
		}
		
		$onclickParams = null;
		if ($paramsStrData) $onclickParams = implode(', ', $paramsStrData);
		
		echo ', '.$onclickParams;
	}
}












if (! function_exists('dateFormatter')) {
	/**
	 * Конвертирует дату в формат по правилам Carbon
	 * @param string|null  $date строка даты
	 * @param string|null  $format формат
	 * @return string|null
	*/
	function dateFormatter(?string $date = null, ?string $format = null) {
		if (!$date || !$format) return '';
		echo now()->parse($date)->format($format);
	}
}
















//------------------------------------------------------------



if (! function_exists('encodeEmail')) {
	/**
	 * Конвертирует Email адрес из кириллицы в UTF-8
	 * @param string|null  $address
	 * @return string
	*/
	function encodeEmail(?string $address): string {
		if (!$address) return (string)$address;
		$encoder = new IdnAddressEncoder();
		return $encoder->encodeString($address);
	}
}




if (! function_exists('decodeEmail')) {
	/**
	 * Конвертирует Email адрес обратно в кириллицу
	 * @param string|null  $address
	 * @return string
	*/
	function decodeEmail(?string $address): string {
		if (!$address) return (string)$address;
		$i = strrpos($address, '@');
        if (false !== $i) {
            $local = substr($address, 0, $i);
            $domain = substr($address, $i + 1);
            $address = sprintf('%s@%s', $local, idn_to_utf8($domain, \IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI | \IDNA_CHECK_CONTEXTJ | \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46));
        }
        return (string)$address;
	}
}


