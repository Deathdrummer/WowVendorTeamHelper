<?php namespace App\Exports;

use App\Enums\OrderStatus;
use App\Exports\Sheets\EventTypeSheet;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EventsExport implements WithMultipleSheets {
    use Exportable;
    
	private $params;
	
    public function __construct(?array $params = null) {
		$this->params = $params;
    }

    /**
     * @return array
     */
    public function sheets(): array {
        $sheets = [];
		
		if ($this->params['type'] == 'all') {
			
			$dateFrom = DdrDateTime::buildTimestamp($this->params['date_from'], ['shift' => true]);
			$dateTo = DdrDateTime::buildTimestamp($this->params['date_to'], ['shift' => true]);
			
			$orders = Order::select('order', 'raw_data', 'status', 'date')
				->where('date', '>=', $dateFrom)
				->where('date', '<', $dateTo)
				->get()
				->makeHidden(['date_msc'])->mapToGroups(function($item, $key) {
					$status = $item['status'];
					$item['date'] = DdrDateTime::shift($item['date'], 'TZ');
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
				
				$sheets[] = new EventTypeSheet($orders[$status] ?? [], $listName);
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