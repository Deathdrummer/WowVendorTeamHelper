<?php namespace App\Exports\ExcelExport;

class Coordinates {
	private $cols = [];
	const COLSDEFINE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	public function __construct() {
		$this->cols = str_split(self::COLSDEFINE);
		
		$p = 0;
		$n = count($this->cols);
		
		for ($i=1; $i<=$n; $i++) {
			$p += pow($n, $i);
		}
	}
	
	
	public function get($col) {
		if (is_numeric($col)) {
			return $this->getLabel($col);
		} else {
			return $this->getIndex($col);
		}
	}


	private function getIndex($col) {
		$colChar = str_split(strtoupper($col));
		$n = count($this->cols);

		if (count($colChar)==2) {
			return ($n*(array_search($colChar[0], $this->cols) + 1)) + (array_search($colChar[count($colChar) - 1], $this->cols) + 1);
		} elseif (count($colChar)>2) {
			$col = substr($col, 0, strlen($col)-1);
			return ($n * $this->getIndex($col))+(array_search($colChar[count($colChar) - 1], $this->cols) + 1);
		} else {
			return array_search($colChar[count($colChar) - 1], $this->cols) + 1;
		}
	}

	private function getLabel($col) {
		$n = count($this->cols);   
		$name = '';

		while ($col > 0) {
			$m = ($col-1) % $n;
			$name = $this->cols[$m].$name;
			$col = floor(($col - $m) / $n);
		}
		return $name;
	}
}