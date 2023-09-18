<?php namespace App\Http\Controllers\Business;

use App\Enums\LogEventsGroups;
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
	public function index(Request $request) {
		[
			'page'	=> $page,
			'group'	=> $group,
		] = $request->validate([
			'page'	=> 'required|numeric',
			'group'	=> 'required|numeric',
		]);
		
		$query = EventLog::query()
			->with('author:id,name,pseudoname')
			->with('adminauthor:id,name,pseudoname')
			//->with('author:id,name,pseudoname', 'adminauthor:id,name,pseudoname')
			->where('group', (int)$group)
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
		
		
		$view = LogEventsGroups::fromValue((int)$group)->key;
		
		return response()->view('admin.section.system.render.logs.'.$view, compact('data'))->withHeaders($headers);
	}
	
	
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function info(Request $request) {
		[
			'id'	=> $id,
		] = $request->validate([
			'id'	=> 'required|numeric',
		]);
		
		$row = EventLog::find($id);
		$infoData = collect(($row->info));
		
		$hasUpdated = $infoData->contains(function ($value, $key) {
			return array_key_exists('updated', $value);
		});
		
		$data = $infoData->sortBy('sort');
		
		return response()->view('admin.section.system.render.logs.info', compact('data', 'hasUpdated'))->withHeaders(['hasUpdated' => $hasUpdated]);
	}
	
	
	
}