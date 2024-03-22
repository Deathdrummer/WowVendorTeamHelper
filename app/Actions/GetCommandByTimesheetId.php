<?php namespace App\Actions;

use App\Models\Command;
use App\Models\Timesheet;

class GetCommandByTimesheetId {
	/**
	* 
	* @param 
	* @return 
	*/
	public function __invoke($timesheetId = null, $returnField = null) {
		if (!$timesheetId) return false;
		$timesheetData = Timesheet::find($timesheetId);
		
		if (!$commandData = Command::where('id', $timesheetData['command_id'])->first()) return false;
		
		if ($returnField) {
			return $commandData[$returnField] ?? null;
		}
		
		return $commandData;
	}
}