<?php namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Timesheet;
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
		
		$periods = TimesheetPeriod::whereIn('id', $periodsIds)->pluck('title', 'id');
		
		
		$data = Timesheet::with(['orders' => function($q) {
				$q->select('id', 'price')
					->where(function($v) {
						$v->where(['timesheet_order.cloned' => null, 'timesheet_order.doprun' => null]);
					})->orWhere(function($v) {
						$v->where(['timesheet_order.cloned' => null, 'timesheet_order.doprun' => 1]);
					});
			}])
			->whereIn('timesheet_period_id', $periodsIds)
			->get();
		
		if (!$data) return response()->json(false);
	
		
		
		$map = [];
		$data->each(function($row) use(&$map) {
			if (!isset($map[$row['command_id']][$row['timesheet_period_id']])) $map[$row['command_id']][$row['timesheet_period_id']] = 0;
			$map[$row['command_id']][$row['timesheet_period_id']] += (float)$row->orders->sum('price');
			
			if (!isset($map[$row['command_id']]['all'])) $map[$row['command_id']]['all'] = 0;
			$map[$row['command_id']]['all'] += (float)$row->orders->sum('price');
			
			if (!isset($map['periods'][$row['timesheet_period_id']])) $map['periods'][$row['timesheet_period_id']] = 0;
			$map['periods'][$row['timesheet_period_id']] += (float)$row->orders->sum('price');
			
			if (!isset($map['total'])) $map['total'] = 0;
			$map['total'] += (float)$row->orders->sum('price');
		});
		
		$commands = Command::whereIn('id', array_keys($map))->get()->pluck('title', 'id');
		
		return response(view($viewPath.'.report', compact('periods', 'map', 'commands')));
	}

}