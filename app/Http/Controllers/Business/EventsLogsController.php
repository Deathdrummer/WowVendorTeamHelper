<?php namespace App\Http\Controllers\Business;

use App\Enums\LogEventsTypes;
use App\Http\Controllers\Controller;
use App\Models\EventLog;
use App\Traits\HasPaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EventsLogsController extends Controller {
	use HasPaginator;
	/* public function __construct() {
	} */



	/**
	* 
	* @param 
	* @return 
	*/
	public function __invoke(Request $request) {
		$page = $request->input('page', 0);
		
		$query = EventLog::query()
			->with('author:id,name,pseudoname')
			->with('adminauthor:id,name,pseudoname')
			//->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname')
			->orderBy('id', 'DESC');
		
		$paginate = $this->paginate($query, $page, 15)->toArray();
		['data' => $data] = array_splice($paginate, array_search('data', array_keys($paginate)), 1);
		
		if ($data ?? false) {
			foreach ($data as $k => $row) {
				$data[$k]['from'] = match($row['user_type']) {
					1		=> $row['author'] ?? null,
					2		=> $row['adminauthor'] ?? null,
					default	=> null,
				};
				
				$eventType = json_decode(LogEventsTypes::fromValue($row['event_type'])->description ?? null, true);
				
				$data[$k]['event_type'] = $eventType[App::currentLocale()];
				
				unset($data[$k]['author'], $data[$k]['adminauthor']);
			}
		}
		
		$headers = [
			'current_page'	=> $paginate['current_page'] ?? null,
			'per_page'		=> $paginate['per_page'] ?? null,
			'last_page'		=> $paginate['last_page'] ?? null,
			'total'			=> $paginate['total'] ?? null,
		];
		
		return response()->view('admin.section.system.render.logs.list', compact('data'))->withHeaders($headers);
	}
	
	
	
	
	
}