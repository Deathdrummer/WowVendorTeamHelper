<?php namespace App\View\Components;

use App\Traits\Settingable;
use Illuminate\View\Component;

class Simplelist extends Component {
	use Settingable;
    
	public $fieldsToButton;
	public $fields = [];
	public $titles = [];
	public $stringOptions = null;
	public $options = [];
	public $onRemove = null;
	public $onCreate = null;
	
	
	/**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(?string $fieldset = null, ?string $options = null, ?string $onCreate = null, ?string $onRemove = null) {
		if (!$fieldset) return false;
		
		if (!$fieldsData = array_filter(splitString($fieldset, ','))) return false;
		
		$titles = []; $fields = []; $fieldsToBtn = [];
		foreach ($fieldsData as $field) {
			$d = splitString($field, '|');
			
			$title = $d[0] ?? null;
			$type = $d[1] ?? null;
			$name = $d[2] ?? null;
			$readStat = $d[3] ?? null;
			
			$titles[] = splitString($title, ':');
			
			$fields[] = [
				'type' 		=> $type, 
				'name' 		=> $name,
				'readonly' 	=> $readStat,
			];
			
			$fieldsToBtn[] = $type.':'.$name;
		}
		
		$this->fields = $fields;
		$this->titles = $titles;
		$this->fieldsToButton = implode('|', $fieldsToBtn);
		$this->onCreate = $onCreate;
		$this->onRemove = $onRemove;
		
		$this->setDataToSelect($options);
    }
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function settingData($settings = false, $setting = false) {
		if (!$setting || !$settings) return [];
		if (!isset($settings[$setting])) return [];
		
		$dataList = $settings[$setting];
		
		ksort($dataList, SORT_NUMERIC);
		
		return $dataList;
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function setDataToSelect($options) {
		if (!$options) return false;
		$this->stringOptions = str_replace(',', '&&&', $options);
		
		$opsData = splitString($options, '|');

		$allOpsData = [];
		if ($opsData) {
			foreach ($opsData as $ops) {
				[$name, $values] = splitString($ops, ';');
				
				if (preg_match('/\bsetting::([\w\,]+)\b/', $values, $matches)) {
					$setting = $matches[1] ?? false;
					$params = explode(',', $setting);
					$opsValues = $this->getSettings(...$params);
					
					if ($opsValues) {
						foreach ($opsValues as $val => $title) {
							$allOpsData[$name][$val] = $title ?? $val;
						}
					}
				} else {
					$opsValues = splitString($values, ',');
					if ($opsValues) {
						foreach ($opsValues as $optVal) {
							$o = splitString($optVal, ':');
							$allOpsData[$name][$o[0]] = $o[1] ?? $o[0];
						}
					}
				}
			}
		}
		
		$this->options = $allOpsData;
	}
	
	


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render() {
        return view('components.simplelist.index');
    }
}