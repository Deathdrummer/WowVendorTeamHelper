<?php namespace App\Http\Requests;

use App\Helpers\DdrDateTime;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Timesheet;
use App\Services\Settings;
use Illuminate\Foundation\Http\FormRequest;

class ImportTimesheetEventsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }
	

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        return [
			'period_id' => 'required|numeric',
			'file' 		=> 'required|file',
			//'separator'	=> 'required|string',
        ];
    }
	
	
	
	/**
	* 
	* @param
	* @return
	*/
	public function importEvents() {
		$fileData = $this->file->get();
		
		if (!$rows = array_filter(splitString($fileData, "\n"))) return false;
		
		$settings = app()->make(Settings::class);
		
		$difficulties = $settings->get('difficulties')?->mapWithKeys(function ($item, $key) {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		$eventsTypes = EventType::get()?->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->flip()->toArray();
		
		$commands = Command::get()?->mapWithKeys(function ($item, $key)  {
    		return [$item['id'] => $item['title']];
		})->flip()->toArray();
		
		$maxSort = Timesheet::max('_sort');
		
		$importData = [];
		foreach($rows as $k => $row) {
			$splitRow = preg_split('/\s+/', $row); 
			
			if (count($splitRow) == 4) [$date, $time, $commandId, $eventTypeId] = $splitRow;
			else continue;
			
			if (!isset($commands[$commandId]) || !isset($eventsTypes[$eventTypeId])) continue;
			
			$importData[] = [
				'command_id' 			=> $commands[$commandId],
				'event_type_id' 		=> $eventsTypes[$eventTypeId],
				'timesheet_period_id' 	=> (int)$this->period_id,
				'datetime' 				=> DdrDateTime::buildTimestamp($date, $time, ['shift' => true]),
				'_sort'					=> ++$maxSort,
			];
		}
		
		$res = Timesheet::insert($importData);
		return $res;
	}
	
	
}