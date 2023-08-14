<?php namespace App\Exports\Sheets;

use App\Traits\Settingable;
use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;


class EventTypeSheet extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromArray, WithStyles, WithEvents, WithProperties, WithTitle/*, WithColumnWidths, WithDefaultStyles, WithBackgroundColor */ {	
	use Settingable;
	
	private $orders = [];
	private $columsNames = [];
	private $listName;

    public function __construct($orders, $listName) {
        $this->listName = $listName;
		$this->orders = $orders;
		
		$this->columsNames = [
			'order'		=> 'Номер заказа',
			'raw_data'	=> 'Тело заказа',
		];
		
		
    }
	
	
	
	/** Задать метаданные
     * @return array
     */
	public function properties(): array {
        return [
            'creator'        => 'WowVendorTeamHelper',
            'title'          => 'WowVendorTeamHelper договоры',
            'description'    => 'WowVendorTeamHelper список заказов',
            'company'        => 'WowVendorTeamHelper',
        ];
    }
	
	
	
	
	/** Задать наазвание вкладки
     * @return string
     */
    public function title():string {
        return $this->listName;
    }
	
	
	
	
	
	

	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
    public function array():array {
		return [
			$this->columsNames,
			$this->orders,
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
		$workSheet->freezePane('A2');
    }
	
	
	
	
	
	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
	public function styles(Worksheet $sheet):array {
		[$colsInfo, $lastColCoord] = $this->getColumsInfo();
		$lastRow = count($this->orders) + 1;
		
		
		
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
		
		
		// rows начиная со 2 строки
		for ($row = 2; $row <= $lastRow; $row++) {
			$sheet->getRowDimension($row)->setRowHeight(35);
		}
		
		
		return [
			1	=>	['fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEFFF1'],
            ],
		]];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------------------------------------------------------
	
	
	
	
	
	/** Сформировать массв даных о столбцах
	 * @param 
	 * @return 
	 */
	private function getColumsInfo() {
		// Выписываются все поля, что могут быть использованы и задаются настройки для каждого поля
		$columsData = [
			'order'		=> ['width' => 10, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'number'],
			'raw_data'	=> ['width' => 90, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_TOP, 'wrap' => true, 'type' => 'text'],
		];
		
		
		
		$intersectedData = [];
		foreach ($this->columsNames as $field => $name) {
			$intersectedData[$field] = $columsData[$field] ?? null;
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
