<?php namespace App\Exports;

use App\Enums\OrderStatus;
use App\Exports\Sheets\DdrSheet;
use App\Exports\Sheets\EventTypeSheet;
use App\Helpers\DdrDateTime;
use App\Models\Order;
use App\Models\Timesheet;
use App\Models\TimesheetOrder;
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
		
		if ($this->params['type'] == 'all') { //------------------------------ Выгрузка входящего потока по дате
			
			$dateFrom = DdrDateTime::buildTimestamp($this->params['date_from'], ['shift' => -3]);
			$dateTo = DdrDateTime::buildTimestamp($this->params['date_to'], '23:59:59', ['shift' => -3]);
			
			$incomingOrders = []; $manuallyOrders = [];
			$orders = Order::select('order', 'raw_data', 'status', 'date_add', 'created_at', 'manually')
				->where(function ($query) use($dateFrom, $dateTo) {
					$query->where('date_add', '>=', $dateFrom)->where('date_add', '<=', $dateTo);
				})
				->orWhere(function ($query) use($dateFrom, $dateTo) {
					$query->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo);
				})
				->get()
				->makeHidden(['date_msc'])
				->mapToGroups(function($item, $key) use(&$manuallyOrders, &$incomingOrders) {
					$status = $item['status'];
					$manually = $item['manually'];
					$item['date'] = Carbon::parse($item['date_add'] ?? $item['created_at'])->format('Y-m-d H:i');
					unset($item['status'], $item['date_add'], $item['created_at'], $item['manually']);
					
					$incomingOrders[] = $item->toArray();
					
					if ($manually) $manuallyOrders[] = $item->toArray();
					
					return [$status => $item];
				});
			
			$listsNames = [
				'wait'		=> 'Лист ожидания',
				'cancel'	=> 'Отмененные',
			];
			
			$sheets[] = new EventTypeSheet($incomingOrders, 'Входящие'); // Добавляем к массиву заказов все созданные заказы
			
			foreach ($listsNames as $statName => $listName) {
				$status = OrderStatus::fromKey($statName)?->value;
				$sheets[] = new EventTypeSheet($orders[$status] ?? [], $listName);
			}
			
			$sheets[] = new EventTypeSheet($manuallyOrders, 'Созданные заказы'); // Добавляем к массиву заказов заказы, созданные вручную
			
			
		} else if ($this->params['type'] == 'linked') { //------------------------------ экспорт данных за выбранный период
			
			$periodId = $this->params['period_id'];
			
			
			$data = Timesheet::where('timesheet_period_id', $periodId)->with('orders')->with('command')->get();
			
			$timesheetIds = $data->pluck('id');
			$doprunOrders = [];
			TimesheetOrder::whereIn('timesheet_order.order_id', function($builder) use($timesheetIds) {
				$builder->select('timesheet_order.order_id')->from('timesheet_order')->whereIn('timesheet_order.timesheet_id', $timesheetIds);
			})
			->where('doprun', 1)
			->get()
			->each(function($row) use(&$doprunOrders) {
				if (!isset($doprunOrders[$row['order_id']])) $doprunOrders[$row['order_id']] = 0;
				$doprunOrders[$row['order_id']] += 1;
			});
			
			
			$pricessMap = [];
			foreach ($data->toArray() as $ts) {
				$totalSum = [];
				foreach ($ts['orders'] as $k => $order) {
					$status = $order['status'] ?? 0;
					$isDopRun = !!($order['pivot']['doprun'] ?? false);
					if (!isset($totalSum[$status])) $totalSum[$status] = 0;
					if ($isDopRun) {
						if (!isset($totalSum[OrderStatus::doprun])) $totalSum[OrderStatus::doprun] = 0;
						$totalSum[OrderStatus::doprun] += (float)($order['price'] ?? 0) / (int)($doprunOrders[$order['id']] ?? 1);
					} 
					$totalSum[$status] += (float)($order['price'] ?? 0) / (int)($doprunOrders[$order['id']] ?? 1);
				}
				
				$pricessMap[$ts['id']] = $totalSum;
			}
			
			
			$buildData = [];
			foreach ($data->toArray() as $ts) {
				$hasDopRun = false;
				
				foreach ($ts['orders'] as $k => $order) {
					//if ($k == 0) $buildData[$order['status']][$k] = [];
					$command = $k == 0 ? $ts['command']['title'] : null;
					
					$isDopRun = !!($order['pivot']['doprun'] ?? false);
					$isCloned = !!($order['pivot']['cloned'] ?? false);
					$status = $order['status'] ?? 0;
					
					$hasDopRun = !$hasDopRun ? $isDopRun : $hasDopRun;
					
					if (!$isCloned) {
						$buildData[$status][] = [
							$command,
							$k == 0 ? ($pricessMap[$ts['id']][$status].' $' ?? '') : '',
							$order['order'],
							$order['raw_data'],
							Carbon::parse($order['date_add'] ?? $order['created_at'])->format('Y-m-d H:i'),
							$order['last_comment']['message'] ?? '',
						];
					}
					
					
					if ($isDopRun) {
						$buildData[OrderStatus::doprun][] = [
							$command,
							$k == 0 ? ($pricessMap[$ts['id']][OrderStatus::doprun].' $' ?? '') : '',
							$order['order'],
							$order['raw_data'],
							Carbon::parse($order['date_add'] ?? $order['created_at'])->format('Y-m-d H:i'),
							$order['last_comment']['message'] ?? '',
						];
					}
					
					if ($k + 1 == count($ts['orders'])) {
						$buildData[$status][] = ['','','','',''];
						if ($isDopRun) $buildData[OrderStatus::doprun][] = ['','','','',''];
						
						if ($hasDopRun) {
							$buildData[OrderStatus::doprun][] = ['','','','','',];
							$hasDopRun = false;
						}
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