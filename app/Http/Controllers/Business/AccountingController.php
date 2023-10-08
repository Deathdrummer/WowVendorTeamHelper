<?php namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Timesheet;
use App\Models\TimesheetOrder;
use App\Models\TimesheetPeriod;
use Illuminate\Http\Request;

class AccountingController extends Controller {

	/**
	* 
	* @param 
	* @return 
	*/
	public function index(Request $request) {
		[
			'periods_ids'	=> $periodsIds,
			'views'			=> $viewPath,
		] = $request->validate([
			'periods_ids'	=> 'required|array',
			'views'			=> 'required|string',
		]);
		
		$allPeriods = TimesheetPeriod::whereIn('id', $periodsIds)->pluck('title', 'id');
		
		
		
		$data = Timesheet::with(['orders' => function($q) {
				$q->select('id', 'price');
					/* ->where(function($v) {
						$v->where(['timesheet_order.cloned' => null, 'timesheet_order.doprun' => null]);
					})->orWhere(function($v) {
						$v->where(['timesheet_order.cloned' => null, 'timesheet_order.doprun' => 1]);
					}); */
			}])
			->whereIn('timesheet_period_id', $periodsIds)
			->get();
		
		//toLog($data->toArray());

		if (!$data) return response()->json(false);
		
		$timesheetCommand = $data->mapWithKeysMany(function($item) {
			return [$item['id'] => $item['command_id']];
		});
		
		
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
		
		
		
		$map = [];
		$data->each(function($row) use(&$map, $doprunOrders) {
			$row->orders->each(function($order) use(&$map, $row, $doprunOrders) {
				if (!isset($map[$row['id']][$row['timesheet_period_id']])) $map[$row['id']][$row['timesheet_period_id']] = 0;
				$map[$row['id']][$row['timesheet_period_id']] += (float)$order['price'] / (int)($doprunOrders[$order['id']] ?? 1);
				
				if (!isset($map[$row['id']]['all'])) $map[$row['id']]['all'] = 0;
				$map[$row['id']]['all'] += (float)$order['price'] / (int)($doprunOrders[$order['id']] ?? 1);
				
				if (!isset($map['periods'][$row['timesheet_period_id']])) $map['periods'][$row['timesheet_period_id']] = 0;
				$map['periods'][$row['timesheet_period_id']] += (float)$order['price'] / (int)($doprunOrders[$order['id']] ?? 1);
				
				if (!isset($map['total'])) $map['total'] = 0;
				$map['total'] += (float)$order['price'] / (int)($doprunOrders[$order['id']] ?? 1);
			});
			
		});
		
		//toLog($map);
		
		$test = [];
		foreach ($map as $tsId => $periods) {
			if (in_array($tsId, ['periods', 'total'])) {
				$test[$tsId] = $periods;
				continue;
			}
			foreach ($periods as $period => $sum) {
				if (!isset($test[$timesheetCommand[$tsId]][$period])) $test[$timesheetCommand[$tsId]][$period] = 0;
				$test[$timesheetCommand[$tsId]][$period] += $sum;
			}
		}
		
		//toLog($test);
		
		$commands = Command::whereIn('id', array_keys($test))->get()->pluck('title', 'id');
		
		return response(view($viewPath.'.report', compact('allPeriods', 'test', 'commands', 'timesheetCommand')));
	}

}