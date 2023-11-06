<?php namespace App\Exports\ExcelExport;

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
use PhpOffice\PhpSpreadsheet\Style\Border;

class Sheet extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromArray, WithStyles, WithEvents, WithProperties, WithTitle/*, WithColumnWidths, WithDefaultStyles, WithBackgroundColor */ {	
	use Settingable;
	
	
	
	/* 'meta'	=> [
		'cell_height' => 30, // Высота ячеек
		'freeze' => 1, // Зафиксировать строки
		'wrap' => true, // Перенос строк
	],
	'cols'	=> [
		'order'		=> ['title' => 'Номер заказа', 'width' => 10, 'bg' => '#777', 'color' => '#fff', 'horizontal' => 'center', 'vertical' => 'top', 'type' => 'number'],
		'raw_data'	=> ['title' => 'Тело заказа', 'width' => 90, 'horizontal' => 'left', 'vertical' => 'top', 'wrap' => true, 'type' => 'text'],
		'date'		=> ['title' => 'Дата поступления заказа', 'width' => 24, 'horizontal' => 'left', 'vertical' => 'top', 'wrap' => true, 'type' => 'text'],

	],
	'data'	=> [
		
	], */
	
	
	//joins
	/* Array
	(
		[horizontal] => 
		[vertical] => Array
			(
				[0] => Array
					(
						[start] => Array
							(
								[row] => 4
								[col] => 1
							)

						[end] => Array
							(
								[row] => 5
							)
					)
			)
	) */
	
	
	
	
	
	
	
	
	
	//private $orders = [];
	//private $columsData = [];
	//private $useColums = [];
	
	//private $contentRowsHeight = 35; # Высота контентных строк
	//private $mergeCols = [];
	
	
	private $labelName;
	private $properties;
	private $cols = [];
	private $titles = [];
	private $data = [];
	private $joins = [];
	public static $freezeRows = null; # количество строк для заморозки
	private $cellContentHeight; # Высота всех строк по-умолчанию
	private $cellTitlesHeight; # Высота первой строки 
	
	
    public function __construct(?string $labelName = null, ?array $properties = [], ?array $cols = null, ?array $titles = null, ?array $data = null, ?array $joins = null, ?array $meta = null) {
        $this->labelName = $labelName;
		$this->properties = $properties;
		
		$this->cols = $cols;
		$this->titles = $titles;
		$this->data = $data;
		$this->joins = $joins;
		
		self::$freezeRows = match(true) {
			isset($meta['freeze']) && $meta['freeze'] === true => count($this?->titles) ?? 0,
			isset($meta['freeze']) && is_numeric($meta['freeze']) => (int)$meta['freeze'],
			default	=> false,
		};
		
		$this->cellTitlesHeight = $meta['titles_height'] ?? null;
		$this->cellContentHeight = $meta['cell_height'] ?? null;
		
		
		
		
		//
		//$this->orders = $orders;
		
		
		// Выписываются все поля, что могут быть использованы и задаются настройки для каждого поля
		/* $this->columsData = [
			'command'		=> ['name' => 'Команда', 'width' => 15, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'text'],
			'price'			=> ['name' => 'Общая сумма', 'width' => 12, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'number'],
			'order'			=> ['name' => 'Номер заказа', 'width' => 10, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP, 'type' => 'number'],
			'raw_data'		=> ['name' => 'Тело заказа', 'width' => 90, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_TOP, 'wrap' => true, 'type' => 'text'],
			'date'			=> ['name' => 'Дата поступления заказа', 'width' => 24, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_TOP, 'wrap' => true, 'type' => 'text'],
			'last_comment'	=> ['name' => 'Последний комментарий', 'width' => 50, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_TOP, 'wrap' => true, 'type' => 'text'],
		]; */
		
		// Какие столцы выводить в таблице (если в 2 и более строк - то указывать в виде массивов)
		/* $this->useColums = [
			'command',
			'price',
			'order',
			'raw_data',
			'date',
			'last_comment',
		]; */
		
		# Объединить ячейки
		//$this->mergeCols = [/* 'A2:B2' */];
		
    }
	
	
	
	/** Задать метаданные
     * @return array
     */
	public function properties():array {
        return $this->properties;
    }
	
	
	
	/** Задать наазвание вкладки
     * @return string
     */
    public function title():string {
        return $this->labelName;
    }
	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
    public function array():array {
		$data = [];
		
		if ($titles = $this->_buildTitles()) {
			$data[] = [...$titles];
		}
		
		# формирование массива данных, удаление META данных ячеек
		if ($this->data) {
			$buildedData = [];
			foreach ($this->data as $row => $cellsData) foreach ($cellsData['data'] as $cellKey => $cellData) {
				$buildedData[$row][$cellKey] = is_array($cellData) ? ($cellData['data'] ?? null) : $cellData;
			}
		}
		
		$data[] = [...$buildedData];
		
		return $data;
    }
	
	
	
	
	
	
	
	/**
    * @return \Illuminate\Support\Array
    */
	public function styles(Worksheet $sheet):array {
		
		# Получить координаты начала и конца списков заголовков и контента
		['titles' => $titlesCoords, 'content' => $contentCoords] = $this->_getContentCoordinates();
		
		
		# Высота строк заголовков
		if ($this->cellTitlesHeight) $sheet->getRowDimension('1')->setRowHeight((int)$this->cellTitlesHeight);
		
		
		# Высота контентных строк
		if ($this->cellContentHeight) {
			for ($x = count($this->titles); $x < count($this->data) + count($this->titles); $x++) {
				$sheet->getRowDimension($x+1)->setRowHeight((int)$this->cellContentHeight);
			}
		}
		
		
		
		
		# Объединить ячейки
		if ($this->joins) {
			foreach ($this->joins as $type => $joinsData) {
				foreach ($joinsData as ['start' => $start, 'end' => $end]) {
					$merge = null;
					if ($type == 'vertical') {
						$colCoord = $this->getFieldCoords($start['col']);
						$merge = $colCoord.$start['row'].':'.$colCoord.$end['row'];
					}
					
					if ($type == 'horizontal') {
						$colCoordStart = $this->getFieldCoords($start['col']);
						$colCoordEnd = $this->getFieldCoords($end['col']);
						$merge = $colCoordStart.$start['row'].':'.$colCoordEnd.$start['row'];
					}
					
					if ($merge) $sheet->mergeCells($merge);
				}	
			}
		}
		
		
		
		
		
		# Размер и толщина шрифта контентной части
		$sheet->getStyle($contentCoords['joined'])->getFont()->setSize(12)->setBold(false);
		
		
		
		
		
		
		# Стили для заголовков и столбцов
		if ($this?->titles) {
			# Размер и толщина шрифта заголовков
			$sheet->getStyle($titlesCoords['joined'])->getFont()->setSize(12)->setBold(true);
			
			foreach ($this?->titles as $row => $titles) {
				foreach ($titles as $index => $titleMeta) {
					$coord = $this->getFieldCoords($index + 1);
					$type = $titleMeta['type'];
					unset($titleMeta['type']);
					$colMeta = $this->cols[$type] ?? null;
					
					# $colMeta
					# 'width' => 10,
					# 'horizontal' => 'center',
					# 'vertical' => 'top',
					# 'bg' => '#777',
					# 'color' => '#fff',
					# 'type' => 'number',
					
					
					
					# $titleMeta
					# 'title' => NULL,
					# 'horizontal' => 'center',
					# 'vertical' => 'top',
					# 'cell_bg' => '#777',
					# 'col_bg' => '#777',
					# 'color' => '#fff'
					
					
					
					
					
					# Ширина столбца
					if (isset($colMeta['width'])) $sheet->getColumnDimension($coord)->setWidth($colMeta['width'] ?: -1);
					
					
					# Координаты столбца заголовков
					$titleColumnCoord = $coord.$titlesCoords['start'][1] == $coord.$titlesCoords['end'][1] ? $coord.$titlesCoords['start'][1] : $coord.$titlesCoords['start'][1].':'.$coord.$titlesCoords['end'][1];
					
					# Координаты столбца контента
					$contentColumnCoord = $coord.$contentCoords['start'][1] == $coord.$contentCoords['end'][1] ? $coord.$contentCoords['start'][1] : $coord.$contentCoords['start'][1].':'.$coord.$contentCoords['end'][1];
					
					
					
					# Перенос и положение текста для заголовков
					$sheet->getStyle($titleColumnCoord)->getAlignment()->setWrapText($titleMeta['wrap'] ?? false);
					$sheet->getStyle($titleColumnCoord)->getAlignment()->setHorizontal($this->_getAligment('horizontal', $titleMeta['horizontal']));
					$sheet->getStyle($titleColumnCoord)->getAlignment()->setVertical($this->_getAligment('vertical', $titleMeta['vertical']));
					
					
					# Перенос и положение текста для контента
					$sheet->getStyle($contentColumnCoord)->getAlignment()->setWrapText($colMeta['wrap'] ?? false);
					$sheet->getStyle($contentColumnCoord)->getAlignment()->setHorizontal($this->_getAligment('horizontal', $colMeta['horizontal']));
					$sheet->getStyle($contentColumnCoord)->getAlignment()->setVertical($this->_getAligment('vertical', $colMeta['vertical']));
					
					
					match($colMeta['type'] ?? null) {
						'date'		=> $sheet->getStyle($contentColumnCoord)->getNumberFormat()->setFormatCode('dd.mm.yyyy'),
						'price'		=> $sheet->getStyle($contentColumnCoord)->getNumberFormat()->setFormatCode('#,##0.00_-"₽"'),
						'number'	=> $sheet->getStyle($contentColumnCoord)->getNumberFormat()->setFormatCode('0'),
						'bool'		=> $sheet->getStyle($contentColumnCoord)->getFont()->setSize(16)->setBold(true)->setName('Wingdings 2')->getColor()->setRGB('FF00B050'),
						'percent'	=> $sheet->getStyle($contentColumnCoord)->getNumberFormat()->setFormatCode('#,##0.00_-"%"'),
						default		=> false,
					};
					
					
					
					
					# фон столбцов
					if (isset($titleMeta['cell_bg'])) {
						$this->_setBg($sheet, $coord.($row+1), $titleMeta['cell_bg']);
					}
					if (isset($titleMeta['col_bg'])) {
						$this->_setBg($sheet, $contentColumnCoord, $titleMeta['col_bg']);
					}
					
					
					
					
					
					
					//$sheet->getStyle($contentCoords['start'][0].'2:'.$column.$lastRow)->getAlignment()->setWrapText($wrap ?? false);
					
					# 'width' => 10,
					# 'horizontal' => 'center',
					# 'vertical' => 'top',
					# 'bg' => '#777',
					# 'color' => '#fff',
					# 'type' => 'number',
					
				}
			}
			
		}
		
		
		
		
		
		# Стили для контентной части
		if ($this?->data) {
			foreach ($this?->data as $row => $cells) {
				$rowIndex = $row + count($this?->titles) + 1;
				$rowMeta = $cells['meta'] ?? null;
				
				if (isset($rowMeta['bg'])) $this->_setBg($sheet, 'A'.$rowIndex.':'.$titlesCoords['end'][0].$rowIndex, $rowMeta['bg']);
				
				
				foreach ($cells['data'] as $cellCol => $cellData) {
					$coord = $this->getFieldCoords($cellCol + 1);
					if (!$cellMeta = $cellData['meta'] ?? null) continue;
					unset($cellData);
					
					if (isset($cellMeta['bg'])) $this->_setBg($sheet, $coord.$rowIndex, $cellMeta['bg']);
					
					//toLog($cellMeta);
					
					
					
					//$type = $titleMeta['type'];
					
					//$colMeta = $this->cols[$type] ?? null;
				}
			}
		}
		
					
		
		
		
		# Задать границы контентной части
		$sheet->getStyle($contentCoords['start'][0].$contentCoords['start'][1].':'.$titlesCoords['end'][0].$contentCoords['end'][1])->applyFromArray([
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					'color' => ['argb' => 'FFD1D0D1'], # цвет FF и код цвета
				],
			],
			/* 'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEFFF1'],
            ] */
		]);
		
		
		
		# Задать границы заголовков
		$sheet->getStyle($titlesCoords['joined'])->applyFromArray([
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					'color' => ['argb' => 'FFD1D0D1'], # цвет FF и код цвета
				],
			],
			/* 'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEFFF1'],
            ] */
		]);
		
		
		
		
		return[];
		
		/* return [
			1	=>	['fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEFFF1'],
            ],
		]]; */
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
     * @return \Illuminate\Support\Array
     */
    public function registerEvents():array {
        return [
            AfterSheet::class => [self::class, 'afterSheet'],          
        ];
    }
	
	
	/** До какой строки (не включительно) заморозить
     * @return void
     */
	public static function afterSheet(AfterSheet $event):void  {
		if (!self::$freezeRows) return;
        $workSheet = $event->sheet->getDelegate();
		$workSheet->freezePane('A'.self::$freezeRows+1);
    }
	
	
	
	
	
	
	
	
	
	
	
	
	//------------------------------------------------------------------------------------
	
	
	/** Сформировать строки заголовков
	* 
	* @param 
	* @return array|null
	*/
	private function _buildTitles():array|null {
		if (!$this->titles) return null;
		
		$titlesRows = [];
		
		foreach ($this->titles as $row => $data) {
			foreach ($data as ['title' => $title, 'type' => $type]) {
				$titlesRows[$row][] = $title ?? '';
			}
		}
		
		return $titlesRows;
	}
	
	
	
	
	/** Получить координаты заголовков и контентной части
	* 
	* @param 
	* @return array
	*/
	private function _getContentCoordinates():array {
		$titlesRowsCount = 0;
		if ($this->titles) {
			$titlesColsCount = count(reset($this->titles));
			$titlesRowsCount = count($this->titles);
			$endTCoord = $this->getFieldCoords($titlesColsCount);
			
			$titlesJoined = match(true) {
				'A' == $endTCoord && 1 == $titlesRowsCount => 'A1',
				default	=> 'A1:'.$endTCoord.$titlesRowsCount,
			};
			
			$titlesCoords = [
				'start' => ['A', 1],
				'end' => [$endTCoord, $titlesRowsCount],
				'joined' => $titlesJoined,
			];
		}
		
		$dataColsCount = count(reset($this->data));
		$dataRowsCount = count($this->data);
		$endCCoord = $this->getFieldCoords($dataColsCount);
		
		$contentJoined = match(true) {
			'A' == $endCCoord && ($titlesRowsCount + 1) == ($dataRowsCount+$titlesRowsCount) => 'A'.($dataRowsCount+$titlesRowsCount),
			default	=> 'A'.($titlesRowsCount + 1).':'.$endCCoord.($dataRowsCount+$titlesRowsCount),
		};
		
		$contentCoords = [
			'start' => ['A', ($titlesRowsCount + 1)],
			'end' => [$endCCoord, ($dataRowsCount+$titlesRowsCount)],
			'joined' => $contentJoined,
		];
		
		return ['titles' => $titlesCoords, 'content' => $contentCoords];
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	private function _getAligment($dir = null, $arg = null) {
		return match(mb_strtoupper($dir.'_'.$arg)) {
			'HORIZONTAL_LEFT'		=> Alignment::HORIZONTAL_LEFT,
			'HORIZONTAL_RIGHT'		=> Alignment::HORIZONTAL_RIGHT,
			'HORIZONTAL_CENTER'		=> Alignment::HORIZONTAL_CENTER,
			'HORIZONTAL_JUSTIFY'	=> Alignment::HORIZONTAL_JUSTIFY,
			'VERTICAL_TOP'			=> Alignment::VERTICAL_TOP,
			'VERTICAL_BOTTOM'		=> Alignment::VERTICAL_BOTTOM,
			'VERTICAL_CENTER'		=> Alignment::VERTICAL_CENTER,
			'VERTICAL_JUSTIFY'		=> Alignment::VERTICAL_JUSTIFY,
			default					=> false,
		};
	}
	
	
	
	
	
	
	
	
	
	/** Задать фон ячеек
	* 
	* @param $sheet
	* @return void
	*/
	private function _setBg($sheet = null, $cellsCoords = null, $bgColor = 'FFFFFF', $fillType = Fill::FILL_SOLID):void {
		if (!$sheet || !$cellsCoords) return;
		$bgColor = str_replace('#', '', $bgColor);
		$sheet->getStyle($cellsCoords)->applyFromArray([
			'fill' => [
				'fillType'   => $fillType,
				'startColor' => ['rgb' => $bgColor],
			]
		]);
	}
	
	
	
	/** Задать границы ячеек
	* 
	* @param $sheet
	* @return void
	*/
	private function _setBorder($sheet = null, $cellsCoords = null, $borderStyle = Border::BORDER_THIN):void {
		if (!$sheet || !$cellsCoords) return;
		$sheet->getStyle($cellsCoords)->applyFromArray([
			'borders' => [
				'allBorders' => [
					'borderStyle' => $borderStyle,
					'color' => ['argb' => 'FFD1D0D1'], # цвет FF и код цвета
				],
			],
		]);
	}
	
	
	
	
	
	/** Получить координаты столбца по индексу ячейки
	 * @param 
	 * @return 
	 */
	private function getFieldCoords($index = null) {
		$coordinates = app()->make(Coordinates::class);
		return $coordinates->get($index);
	}
	
	
}
