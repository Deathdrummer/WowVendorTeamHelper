<?php namespace App\Exports\ExcelExport;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SheetsBuilder implements WithMultipleSheets {
	use Exportable;
	
	private $rawData;
	
	
	/**
	 * @return array
	 */
	public function __construct(?array $rawData = null) {
		$this->rawData = $rawData;
	}
	
	
	/**
	 * @return array
	 */
	public function sheets():array {
		if (!$this->rawData) return [];
		
		$sheets = [];
		
		foreach ($this->rawData as $labelName => $labelData) {
			['joins' => $joins, 'data' => $data] = $this->_buildCells($labelData['data'], count($labelData['titles']) ?: 0); // ['joins' => $joins, 'data' => $data]
			['joins' => $titlesJoins] = $this->_buildCells($labelData['titles'], count($labelData['titles']) ?: 0); // ['joins' => $titlesJoins]
			
			$properties = $labelData['properties'] ?? null;
			$cols = $labelData['cols'] ?? null;
			$titles = $labelData['titles'] ?? null;
			$meta = $labelData['meta'] ?? null;
			
			$sheets[] = new Sheet($labelName, $properties, $cols, $titles, $data, $joins, $titlesJoins, $meta);
		}
		
		return $sheets;
	}
	
	
	
	
	/**
	* 
	* @param $data массив данных ячеек
	* @return array
	*/
	private function _buildCells($data = null, $countTitlesRows = 0):array {
		if (!$data) return null;
		
		$joins = []; $joinIterH = 0; $joinIterV = 0; $prevRow = 0;
		foreach ($data as $rowNum => $rowData) {
			$rowIndex = $rowNum + 1;
			$rowData = $rowData['data'] ?? $rowData;
			foreach ($rowData as $colNum => $colData) {
				
				$colIndex = $colNum + 1;
				if ($colData == '_join_h_') {
					if ($colIndex == 1) {
						$data[$rowNum][$colNum] = null;
						continue;
					}
					if (!isset($joins['horizontal'][$joinIterH]['start'])) {
						$joins['horizontal'][$joinIterH]['start'] = ['row' => $rowIndex, 'col' => $colIndex - 1];
					}
					$joins['horizontal'][$joinIterH]['end'] = ['col' => $colIndex];
					$data[$rowNum][$colNum] = null;
				} else {
					$joinIterH++;
				}
				
				if ($colData == '_join_v_') {
					if ($rowIndex == 1) {
						$data[$rowNum][$colNum] = null;
						continue;
					}
					$prevRow = $rowNum;
					if (!isset($joins['vertical'][$joinIterV]['start'])) {
						$joins['vertical'][$joinIterV]['start'] = ['row' => $rowIndex + $countTitlesRows - 1, 'col' => $colIndex];
					}
					$joins['vertical'][$joinIterV]['end'] = ['row' => $rowIndex + $countTitlesRows];
					$data[$rowNum][$colNum] = null;
				} elseif ($prevRow != $rowNum) {
					$joinIterV++;
				}
			}
		}
		
		// Сбрасить ключи массивов
		foreach ($joins as $jk => $jData) $joins[$jk] = array_values($jData);
		
		// Удалить 
		/* foreach ($joins as $type => $items) {
			foreach ($items as $itemKey => $item) {
				if ($type == 'horizontal' && $item['start']['col'] == $item['end']['col']) {
					unset($joins[$type][$itemKey]);
					if (count($joins[$type]) == 0) $joins[$type] = null;
				}
				
				if ($type == 'vertical' && $item['start']['row'] == $item['end']['row']) {
					unset($joins[$type][$itemKey]);
					if (count($joins[$type]) == 0) $joins[$type] = null;
				}
			}
		} */
		
		return ['joins' => $joins, 'data' => $data];
	}
	
}