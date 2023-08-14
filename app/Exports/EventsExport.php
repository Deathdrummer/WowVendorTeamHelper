<?php namespace App\Exports;

use App\Enums\OrderStatus;
use App\Exports\Sheets\EventTypeSheet;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EventsExport implements WithMultipleSheets {
    use Exportable;
    
	private $type;
	
    public function __construct(?string $type = null) {
		$this->type = $type;
    }

    /**
     * @return array
     */
    public function sheets(): array {
        $sheets = [];
		
		if ($this->type == 'all') {
			
			$orders = Order::select('order', 'raw_data', 'status')->get()->makeHidden(['date_msc'])->mapToGroups(function($item, $key) {
				$status = $item['status'];
				unset($item['status']);
				return [$status => $item];
			});
			
			$listsNames = [
				'new' 			=> 'Входящие',
				'wait' 			=> 'Лист ожидания',
				'cancel' 		=> 'Отмененные',
			];
			
			foreach ($listsNames as $statName => $listName) {
				
				$status = OrderStatus::fromKey($statName)->value;
				
				$sheets[] = new EventTypeSheet($orders[$status], $listName);
			}
			
		}
		
		
		/* $listsNames = [
			'new' 			=> 'Новый',
			'wait' 			=> 'Ожидание',
			'cancel' 		=> 'Отмененный',
			'ready' 		=> 'Готов',
			'doprun' 		=> 'Допран',
			'wait_nosort' 	=> 'Ожидание (Н)',
			'cancel_nosort'	=> 'Отмененные (Н)',
			'all' 			=> 'Вообще все',
		]; */
		
		

        return $sheets;
    }
}