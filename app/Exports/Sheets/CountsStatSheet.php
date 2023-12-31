<?php namespace App\Exports\Sheets;

use App\Traits\Settingable;
use Maatwebsite\Excel\Concerns\FromArray;

//use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Fill;
//use PhpOffice\PhpSpreadsheet\Style\Style;
//use PhpOffice\PhpSpreadsheet\Style\Color;
//use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
//use PhpOffice\PhpSpreadsheet\Style\Border;
//use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
//use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;


class CountsStatSheet extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromArray, WithStyles, WithEvents, WithProperties, WithTitle/*, WithColumnWidths, WithDefaultStyles, WithBackgroundColor */ {	
	use Settingable;
	
	private $buildedData = []; // сформированные данные
	private $contentRowsHeight = 35; # Высота контентных строк
	public static $freezeRows = 1; # количество строк для заморозки
	private $labelTitle = null; // название вкладки
	
	private $columsData = [];
	private $useColums = [];
	private $mergeCols = [];
	
	//------------------------ Пользовательские переменные
	
	

    public function __construct($buildData, $periodTitle, $map, $ordersTypes) {
        $this->labelTitle = $periodTitle;
		// $buildData -> date => order_type => [commands => [command => count], regions => [region => count], all => count]
		// $map -> commands regions
		
		// Выписываются все поля, что могут быть использованы и задаются настройки для каждого поля
		$this->columsData = [
			'date'		=> ['name' => '', 'width' => 15, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'text'],
			'period'	=> ['name' => $periodTitle, 'width' => 30, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'type' => 'text'],
			'cell'		=> ['name' => '', 'width' => 20, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'text'],
		];
		
		
		$fiil = array_fill(0, (count($map['commands']) + count($map['regions']) + 1), 'cell');
		$merge = [...array_column($map['commands'], 'title'), ...array_values($map['regions']), 'all'];
		

		
		// Какие столцы выводить в таблице (если в 2 и более строк - то указывать в виде массивов)
		$this->useColums = [
			'date',
			'period',
			'cell' => $merge,
		];
		
		# Объединить ячейки
		$this->mergeCols = [/* 'A1:C1' */];
		
    }
	
	
	
	/** Задать метаданные
     * @return array
     */
	public function properties(): array {
        return [
            'creator'        => 'WowVendorTeamHelper',
            'title'          => 'WowVendorTeamHelper количество проков',
            'description'    => 'WowVendorTeamHelper статистика количества проков',
            'company'        => 'WowVendorTeamHelper',
        ];
    }
	
	
	
	/** Задать наазвание вкладки
     * @return string
     */
    public function title():string {
        return $this->labelTitle ?? 'Без названия';
    }
	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
    public function array():array {
		return [
			['foo_title', 'bar_title', 'baz_title'],
			['foo', 'bar', 'baz'],
		];
		
		
		$colsNames = [];
		
		// Новая версия
		foreach ($this->useColums as $col) {
			if (is_array($col)) {
				foreach ($col as $colName) {
					$colsNames[] = $colName;
				}
			} else {
				$colsNames[] = $this->columsData[$col]['name'] ?? $col;
			}
		}
		
		
		// Старая версия
		/* foreach ($this->useColums as $row => $colOrRow) {
			if (is_array($colOrRow)) {
				foreach ($colOrRow as $col) {
					$colsNames[$row][] = $this->columsData[$col]['name'] ?? $col;
				}
			} else {
				$colsNames[] = $this->columsData[$colOrRow]['name'] ?? $colOrRow;
			}
		} */
		
		
		return [
			$colsNames,
			$this->buildedData,
		];
    }
	
	
	
	/**
     * @return \Illuminate\Support\Array
     */
    public function registerEvents():array {
        return [
            AfterSheet::class => [self::class, 'afterSheet'],          
        ];
    }
	
	
	
	/**
     * @return void
     */
	public static function afterSheet(AfterSheet $event):void  {
        $workSheet = $event->sheet->getDelegate();
		$workSheet->freezePane('A'.self::$freezeRows+1); # Указать до какой строки (не включительно) заморозить
    }
	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
	public function styles(Worksheet $sheet):array {
		[$colsInfo, $lastColCoord] = $this->getColumsInfo();
		$lastRow = count($this->buildedData) + 1;
		
		
		# Объединить ячейки
		if ($this->mergeCols) {
			foreach ($this->mergeCols as $mergeCol) {
				$sheet->mergeCells($mergeCol);
			}
		}
		
		
		// global
		$sheet->getDefaultRowDimension()->setRowHeight(25);
		$sheet->getRowDimension('1')->setRowHeight(60);
		
		$sheet->getStyle('1:'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('1:'.$lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$sheet->getStyle('1:'.$lastRow)->getAlignment()->setWrapText(true);
		
		$sheet->getStyle('A1:'.$lastColCoord.'1')->getFont()->setSize(10)->setBold(true);
		$sheet->getStyle('A2:'.$lastColCoord.$lastRow)->getFont()->setSize(10);
		
		// #d1d0d1
		
		$sheet->getStyle('A1:'.$lastColCoord.$lastRow)->applyFromArray([
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					'color' => ['argb' => 'FFD1D0D1'],
				],
			],
		]);
		
		
		
		/* $sheet->getStyle('A1:'.$lastColCoord.'1')
			->getBorders()
			->getAllBorders()
    		->setBorderStyle(Border::BORDER_THIN)
			->setColor('Black'); */
		
		/* $sheet->getStyle('A1:'.$lastColCoord.'1')
			->getFill()
    		->setFillType(Fill::FILL_SOLID)
    		->getStartColor()->setRGB('FEFFF1'); */
		
		
		
		
		// column
		foreach ($colsInfo as $field => $colInfo) {
			# $colInfo => ['width' => $width, 'horizontal' => $horizontal, 'vertical' => $vertical, 'wrap' => $wrap, 'type' => $type, 'column' => $column]
			
			extract($colInfo);
			
			$sheet->getColumnDimension($column)->setWidth($width);
			$sheet->getStyle($column.'2:'.$column.$lastRow)->getAlignment()->setHorizontal($horizontal ?? Alignment::HORIZONTAL_LEFT);
			$sheet->getStyle($column.'2:'.$column.$lastRow)->getAlignment()->setVertical($vertical ?? Alignment::VERTICAL_TOP);
			$sheet->getStyle($column.'2:'.$column.$lastRow)->getAlignment()->setWrapText($wrap ?? false);
			
			
			if (isset($type)) {
				if ($type == 'date') {
					$sheet->getStyle($column.'2:'.$column.$lastRow)
						->getNumberFormat()
						->setFormatCode('dd.mm.yyyy');
				
				} elseif ($type == 'price') {
					$sheet->getStyle($column.'2:'.$column.$lastRow)
						->getNumberFormat()
						->setFormatCode('#,##0.00_-"₽"');
				
				} elseif ($type == 'number') {
					$sheet->getStyle($column.'2:'.$column.$lastRow)
						->getNumberFormat()
						->setFormatCode('0');
				
				} elseif ($type == 'bool') {
					$sheet->getStyle($column.'2:'.$column.$lastRow)
						->getFont()
						->setSize(16)
						->setBold(true)
						->setName('Wingdings 2')
						->getColor()->setRGB('FF00B050');;
				
				} elseif ($type == 'percent') {
					$sheet->getStyle($column.'2:'.$column.$lastRow)
						->getNumberFormat()
						->setFormatCode('#,##0.00_-"%"');
				}
			}
		}
		
		
		// контентные rows
		for ($row = $this->getUseColumsRowsCount(); $row <= $lastRow; $row++) {
			$sheet->getRowDimension($row)->setRowHeight($this->contentRowsHeight);
		}
		
		return [
			1	=>	['fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEFFF1'],
            ],
		]];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------------------------------------------------------
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function getUseColumsRowsCount() {
		$rowsCount = 1;
		
		if (is_array($this->useColums[0])) {
			$rowsCount = count($this->useColums);
		}
		
		return $rowsCount;
	}
	
	
	
	
	/** Сформировать массв даных о столбцах
	 * @param 
	 * @return 
	 */
	private function getColumsInfo() {
		$intersectedData = [];
		foreach ($this->useColums as $fieldOrCols) {
			if (is_array($fieldOrCols)) {
				foreach ($fieldOrCols as $field) {
					if (!isset($this->columsData[$field])) continue;
					$intersectedData[$field] = $this->columsData[$field] ?? null;
				}
			} else {
				$intersectedData[$fieldOrCols] = $this->columsData[$fieldOrCols] ?? null;
			}
		}
		
		$coordsIndex = 0;
		$lastColumnCoord = '';
		foreach ($intersectedData as $field => $info) {
			$coord = $this->getFieldCoords(++$coordsIndex);
			$intersectedData[$field]['column'] = $coord;
			$lastColumnCoord = $coord;
		}
		
		return [$intersectedData, $lastColumnCoord];
	}
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	private function getFieldCoords($index = null) {
		if (is_null($index)) return false;
		
		$lettersStr = '|ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$lettersArr = str_split($lettersStr);
		unset($lettersArr[0]);
		
		$maskArr = [];
		
		$ceil = floor($index / 26);
		$remains = $index % 26; 
		
		if ($remains == 0) {
			$ceil = $ceil - 1;
			$remains = 26;
		}
		
		if ($ceil) $maskArr = str_split($ceil);
		$maskArr[] = $remains;
		
		$coordsStr = '';
		foreach ($maskArr as $idx) {
			$coordsStr .= $lettersArr[$idx];
		}
		
		return $coordsStr;
	}
	
	
	
	
}
