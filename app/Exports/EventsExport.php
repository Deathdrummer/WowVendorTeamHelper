<?php namespace App\Exports;

use App\Enums\OrderStatus;
use App\Exports\Sheets\DdrSheet;
use App\Exports\Sheets\EventTypeSheet;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use App\Models\Timesheet;
use Carbon\Carbon;
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
			
			$dateFrom = DdrDateTime::buildTimestamp($this->params['date_from'], ['shift' => -3]);
			$dateTo = DdrDateTime::buildTimestamp($this->params['date_to'], '23:59:59', ['shift' => -3]);
			
			$orders = Order::select('order', 'raw_data', 'status', 'date_add', 'created_at')
				->where(function ($query) use($dateFrom, $dateTo) {
					$query->where('date_add', '>=', $dateFrom)->where('date_add', '<=', $dateTo);
				})
				->orWhere(function ($query) use($dateFrom, $dateTo) {
					$query->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo);
				})
				->get()
				->makeHidden(['date_msc'])
				->mapToGroups(function($item, $key) {
					$status = $item['status'];
					$item['date'] = Carbon::parse($item['date_add'] ?? $item['created_at'])->format('Y-m-d H:i');
					unset($item['status'], $item['date_add'], $item['created_at']);
					return [$status => $item];
				});
			
			
			$listsNames = [
				'new'		=> 'Входящие',
				'wait'		=> 'Лист ожидания',
				'cancel'	=> 'Отмененные',
			];
			
			foreach ($listsNames as $statName => $listName) {
				$status = OrderStatus::fromKey($statName)->value;
				$sheets[] = new EventTypeSheet($orders[$status] ?? [], $listName);
			}
			
		} else if ($this->params['type'] == 'linked') {
			
			$periodId = $this->params['period_id'];
			
			
			$data = Timesheet::where('timesheet_period_id', $periodId)->with('orders')->with('command')->get();
			
			$buildData = [];
			foreach ($data->toArray() as $ts) {
				foreach ($ts['orders'] as $k => $order) {
					//if ($k == 0) $buildData[$order['status']][$k] = [];
					$command = $k == 0 ? $ts['command']['title'] : null;
					
					$buildData[$order['status']][] = [
						$command,
						$order['order'],
						$order['raw_data'],
						Carbon::parse($order['date_add'] ?? $order['created_at'])->format('Y-m-d H:i'),
					];
					
					if ($k + 1 == count($ts['orders'])) {
						$buildData[$order['status']][] = ['','','','',];
					}
				}
			}
			
			$listsNames = [
				'new' 			=> 'Новые',
				'wait' 			=> 'Ожидание',
				'cancel' 		=> 'Отмененные',
				'ready' 		=> 'Готовые',
				'doprun' 		=> 'Допран',
			];
			
			foreach ($listsNames as $statName => $listName) {
				$status = OrderStatus::fromKey($statName)->value;
				$sheets[] = new DdrSheet($buildData[$status] ?? [], $listName);
			}
			
		}

        return $sheets;
    }
}