<?php namespace App\Http\Controllers\Business;

use App\Actions\GetUserSetting;
use App\Actions\LogEventAction;
use App\Actions\UpdateModelAction;
use App\Exports\EventsExport;
use App\Helpers\DdrDateTime;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportTimesheetEventsRequest;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Timesheet;
use App\Services\Settings;
use App\Traits\HasCrudController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TimesheetController extends Controller {
	use HasCrudController;
	
	/**
     * Глобальные данные
     * 	добавляются глобальные данные, которые будут доступны во всех записях списка.
     * 	В списке передается через компонент <x-data>
     * 	В новую запись передается напрямую, без компанента <x-data>
     * 	Данная переменная заполняется автоматически в трейте HasCrudController
     * 	Для добавления данных достаточно просто присвоить их переменной $this->data['название'] = значение (можно отдельно написать метод)
     * 	Для добавления данных из настроек вызвать метод из HasCrudController: 
     * 	$this->addSettingToGlobalData('ключ в настройках[:переименовать ключ]', 'значение в качестве ключа', ['значение в качестве значения'], 'поле для фильтрации[:значение]');
     *
     * @var array
     */
	protected $data = [];
	protected $settings;
	
	
	
	
	public function __construct(Settings $settings) {
		$this->settings = $settings;
		/* 
		$this->middleware('throttle:10,1')->only([
			'store_show',
			'store',
			'update',
			'destroy',
		]);
		
		$this->middleware('lang')->only([
			'index',
			'create',
			'show',
			'store_show',
			'edit',
		]); */
		
	}
	
	
	
	
    /**
     * Вывод всех записей
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, GetUserSetting $getUserSetting, LogEventAction $log) {
		[
			'views'		=> $viewPath,
			'period_id'	=> $periodId,
			'list_type'	=> $listType,
		] = $request->validate([
			'views'		=> 'required|string',
			'period_id'	=> 'required|numeric',
			'list_type'	=> 'required|string',
			'search'	=> 'exclude|nullable|string',
		]);
		
		$search = $request->input('search');
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$timesheetToPastHours = $this->settings->get('timesheet.to_past_hours', 0);
		
		$list = Timesheet::withCount(['orders AS orders_count' => function($query) use($search) {
				$query->where('order', 'LIKE', '%'.$search.'%');
			}])
			->where('timesheet_period_id', $periodId)
			->where(function($query) use($listType, $timesheetToPastHours) {
				if ($listType == 'actual') {
					$query->where('datetime', '>=', now()->addHours(-1 * $timesheetToPastHours));
				} elseif ($listType == 'past') {
					$query->where('datetime', '<', now()->addHours(-1 * $timesheetToPastHours));
				}
			})->when($search, function($query) use($search) {
				$query->whereHas('orders', function($q) use($search) {
					$q->where('order', 'LIKE', '%'.$search.'%');
				});
			})
			->when(isGuard('site') && auth('site')->user()->cannot('razreshit-vse-komandy:site'), function($query) use($getUserSetting) {
				return $query->whereIn('command_id', $getUserSetting('commands') ?? []);
			})
			->orderBy('datetime', $listType == 'actual' ? 'ASC' : 'DESC')
			->get();
		
		
		
		$this->_buildDataFromSettings();
		
		$itemView = $viewPath.'.item';
		
		return $this->viewWithLastSortIndex(Timesheet::class, $viewPath.'.list', compact('list', 'itemView'), '_sort');
    }
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function init(Request $request) {
		[
			'views'		=> $viewPath,
		] = $request->validate([
			'views'		=> 'required|string',
		]);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$scrolled = match(getGuard()) {
			'admin'	=> 'calc(100vh - 238px)',
			'site'	=> 'calc(100vh - 202px)',
			default	=> 'calc(100vh - 238px)',
		};
		
		return $this->view($viewPath.'.init', ['scrolled' => $scrolled]);
	}
	
	
	
	
	
	
	
	
	
	
	
    /**
     * Показ формы создания
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
		[
			'views' => $viewPath,
			'newItemIndex' => $newItemIndex,
		] = $request->validate([
			'views' 		=> 'required|string',
			'newItemIndex'	=> 'required|numeric',
		]);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$this->_buildDataFromSettings();
		$this->data['commands'] = array_column($this->data['commands'], 'title', 'id');
		
		return $this->view($viewPath.'.form', ['index' => $newItemIndex]);
    }
	
	
	
	
	
	

    /**
     * Создание ресурса
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
		$item = $this->_storeRequest($request);
		return response()->json($item);
    }
	
	
	
	/**
     * Создание ресурса и показ записи
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_show(Request $request) {
		if (!$item = $this->_storeRequest($request)) return response()->json(false);
		$viewPath = $request->input('views');
		if (!$viewPath) return response()->json(['no_view' => true]);
		$this->_buildDataFromSettings();
		return $this->view($viewPath.'.item', $item);
    }
	
	
	
	private function _storeRequest($request = null) {
		if (!$request) return false;
		
		$validFields = $request->validate([
			'command_id' 			=> 'required|numeric',
			'event_type_id' 		=> 'required|numeric',
			'date' 					=> 'required|date_format:d-m-Y|exclude',
			'time' 					=> 'required|date_format:H:i|exclude',
			'timesheet_period_id' 	=> 'required|numeric',
			'_sort'					=> 'regex:/[0-9]+/|exclude',
			'views'					=> 'string|exclude',
		]);
		
		$validFields['datetime'] = DdrDateTime::buildTimestamp($request->input('date'), $request->input('time'), ['shift' => true]);
		
		if (!isset($validFields['_sort'])) {
			$maxSort = Timesheet::max('_sort');
			$validFields['_sort'] = $maxSort + 1;
		}
		
		return Timesheet::create($validFields);
	}
	
	
	
	
	

    /**
     * Показ формы редактирования
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
		[
			'views' => $viewPath,
		] = $request->validate([
			'views' => 'required|string',
		]);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$this->_buildDataFromSettings();
		$this->data['commands'] = array_column($this->data['commands'], 'title', 'id');
		
		
		$timesheet = Timesheet::find($id);
		
		return $this->view($viewPath.'.form', $timesheet);
		
    }
	
	
	
	

    /**
     * Обновление ресурса
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
		$validFields = $request->validate([
			'command_id' 			=> 'required|numeric',
			'event_type_id' 		=> 'required|numeric',
			'date' 					=> 'required|date_format:d-m-Y|exclude',
			'time' 					=> 'required|date_format:H:i|exclude',
			//'timesheet_period_id' 	=> 'required|numeric',
			'views'					=> 'required|string|exclude',
			//'list_type'				=> 'required|string|exclude',
		]);
		
		$validFields['datetime'] = DdrDateTime::buildTimestamp($request->input('date'), $request->input('time'), ['shift' => true]);

		$timesheet = Timesheet::find($id);
		$timesheet->fill($validFields);
		$timesheet->save();
		
		$viewPath = $request->input('views');
		$this->_buildDataFromSettings();
		
		return true; //$this->view($viewPath.'.item', $timesheet);
    }
	
	
	
	
	
    /**
     * Удаление записи
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(?int $id = null) {
		if (!$id) return response()->json(false);
		$stat = Timesheet::destroy($id);
		return response()->json($stat);
    }
	
	
	
	
	/**
	* 
	* @param
	* @return
	*/
	public function get_import_form(Request $request) {
		[
			'views' => $viewPath,
		] = $request->validate([
			'views' => 'required|string',
		]);
		
		return response()->view($viewPath.'.import_form');
	}
	
	
	
	/**
	* 
	* @param
	* @return
	*/
	public function import_events(ImportTimesheetEventsRequest $request) {
		$request->validated();
		
		$res = $request->importEvents();

		return response()->json($res);
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function export_orders_form(Request $request) {
		[
			'views' 	=> $viewPath,
		] = $request->validate([
			'views' 	=> 'required|string',
		]);
		
		return response(view($viewPath.'.export_form'));
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param
	* @return
	*/
	public function export_orders(Request $request) {
		if ($request->input('type') == 'all') {
			$params = $request->validate([
				'type'		=> 'required|string',
				'date_from' => 'required|string',
				'date_to'	=> 'required|string',
			]);
		} elseif ($request->input('type') == 'linked') {
			$params = $request->validate([
				'type'		=> 'required|string',
				'period_id' => 'required|numeric',
			]);
		}
		
		return Excel::download(new EventsExport($params), 'orders.xlsx');
	}
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function comment_form(Request $request) {
		[
			'id' 	=> $id,
			'views'	=> $viewPath,
		] = $request->validate([
			'id'	=> 'required|numeric',
			'views'	=> 'required|string',
		]);
		
		$timesheeet = Timesheet::find($id);
		
		return response(view($viewPath.'.comment', ['comment' => $timesheeet?->comment]));
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function comment_save(Request $request, UpdateModelAction $update) {
		[
			'id' 		=> $id,
			'comment'	=> $comment,
		] = $request->validate([
			'id'		=> 'required|numeric',
			'comment'	=> 'required|string',
		]);
		
		$res = $update(Timesheet::class, $id, ['comment' => $comment]);
		
		return response()->json($res);
	}
	
	
	
	
	
	
	
	
	//---------------------------------------------------------------------------------------
	
	
	
	
	/** Задать глобальные настройки
	 * @return void
	 */
	private function _buildDataFromSettings():void {
		$this->addSettingToGlobalData([[
			'setting'	=> 'timezones',
			'key'		=> 'id',
		]]);
		
		$difficulties = $this->settings->get('difficulties')?->mapWithKeys(function ($item, $key) {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		$eventsTypes = EventType::get()?->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
		
		$this->data['events_types'] = $eventsTypes;
		
		$timezones = $this->data['timezones'];
		$commands = Command::get()?->mapWithKeys(function ($item, $key) use($timezones) {
    		$item['shift'] = (int)$timezones[$item['region_id']]['shift'];
    		$item['format_24'] = $timezones[$item['region_id']]['format_24'] ?? 0;
    		$item['timezone'] = $timezones[$item['region_id']]['timezone'] ?? '-';
			return [$item['id'] => $item];
		})->toArray();
		
		$this->data['commands'] = $commands;
	}
	
	
	
	
}