<?php namespace App\Actions;

use App\Exports\ExcelExport\SheetsBuilder;
use Error;
use Maatwebsite\Excel\Facades\Excel;

class ExportToExcelAction {
	/**
	* 
	* @param 
	* @return 
	*/
	public function __invoke($rawData = null, $tableTitle = 'Без Названия') { // $data-> ['meta', 'cols', 'rows']
		if (!$rawData) throw new Error('ExportToExcelAction ошибка! Не переданы данные!');
		return Excel::download(new SheetsBuilder($rawData), $tableTitle.'.xlsx');
	}
	
	
	
	
	
	
}