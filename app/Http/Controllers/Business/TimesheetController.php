<?php namespace App\Http\Controllers\Business;

use App\Actions\GetUserSetting;
use App\Actions\UpdateModelAction;
use App\Enums\OrderStatus;
use App\Exports\EventsExport;
use App\Helpers\DdrDateTime;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportTimesheetEventsRequest;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Timesheet;
use App\Models\TimesheetPeriod;
use App\Services\Business\OrderService;
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
    public function index(Request $request, GetUserSetting $getUserSetting) {
		[
			'views'			=> $viewPath,
			'period_id'		=> $tsPeriodId,
			'list_type'		=> $listType,
			'region_id'		=> $regionId,
		] = $request->validate([
			'views'			=> 'required|string',
			'period_id'		=> 'required|numeric',
			'list_type'		=> 'required|string',
			'region_id'		=> 'required|numeric',
			'search'		=> 'exclude|nullable|string',
			'command_id'	=> 'exclude|nullable|numeric',
			'event_type'	=> 'exclude|nullable|numeric',
		]);
		
		
		$timezones = $this->settings->get('timezones')->where('region', $regionId)->pluck('id')->toArray();
		$regionCommands = Command::whereIn('region_id', $timezones)->get()->pluck('title', 'id');
		$eventsTypes = EventType::all()->pluck('title', 'id');
		
		$search = $request->input('search');
		$commandId = $request->input('command_id');
		$eventType = $request->input('event_type');
		
		//toLog($regionId.' '.$commandId);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$timesheetToPastHours = $this->settings->get('timesheet.to_past_hours', 0);
		
		$timezonesRegions = $this->settings->get('timezones')->filter(fn($row) => $row['region'] == $regionId)->map(function($row) {
			return $row['id'];
		});
		
		$commandsIds = Command::whereIn('region_id', $timezonesRegions)->get()->pluck('id');
		
		
		$list = Timesheet::withCount(['orders AS orders_count' => function($query) use($search) {
				$query->where('order', 'LIKE', '%'.$search.'%');
			}])
			->with(['orders' => function ($query) use($tsPeriodId) {
				$query->where('status', 1)
					->whereNotIn('orders.id', function($builder) use($tsPeriodId) {
						$builder->select('confirmed_orders.order_id')
							->from('confirmed_orders')
							->where('confirmed_orders.confirm', 0)
							->whereIn('confirmed_orders.timesheet_id', function($builder) use($tsPeriodId) {
								$builder->select('timesheet.id')
									->from('timesheet')
									->where('timesheet.timesheet_period_id', $tsPeriodId);
							});
					});
			}])
			->where('timesheet_period_id', $tsPeriodId)
			->where(function($query) use($listType, $timesheetToPastHours) {
				if ($listType == 'actual') {
					$query->where('datetime', '>=', now()->addHours(-1 * $timesheetToPastHours));
				} elseif ($listType == 'past') {
					$query->where('datetime', '<', now()->addHours(-1 * $timesheetToPastHours));
				}
			})
			->whereIn('command_id', $commandsIds)
			->when($commandId, function($query) use($commandId) {
				$query->where('command_id', $commandId);
			})
			->when($eventType, function($query) use($eventType) {
				$query->where('event_type_id', $eventType);
			})
			->when($search, function($query) use($search) {
				$query->whereHas('orders', function($q) use($search) {
					$q->where('order', 'LIKE', '%'.$search.'%');
				});
			})
			->when(isGuard('site') && auth('site')->user()->cannot('razreshit-vse-komandy:site'), function($query) use($getUserSetting) {
				return $query->whereIn('command_id', $getUserSetting('commands') ?? []);
			})
			->orderBy('datetime', $listType == 'actual' ? 'ASC' : 'DESC')
			->get();
		
		
		if ($list) {
			$doprunOrders = [];
			$timesheets = Timesheet::where('timesheet_period_id', $tsPeriodId)->get()->pluck('id');
			foreach ($timesheets as $ts) $doprunOrders[$ts] = OrderService::getOrdersDopruns($ts);
			
			$list = $list->each(function(&$tsRow) use($doprunOrders) {
				$tsRow->orders_sum_price = 0;
				if ($tsRow->orders) {
					$tsRow->orders->each(function(&$oRow) use($doprunOrders, $tsRow) {
						if (isset($doprunOrders[$tsRow->id][$oRow->id])) {
							$tsRow->orders_sum_price += round($oRow->price / $doprunOrders[$tsRow->id][$oRow->id] ?? 1, 2);
						} else {
							$tsRow->orders_sum_price += $oRow->price;
						}
					});
					unset($tsRow->orders);
				}
			});
		}
		
		
		$this->_buildDataFromSettings();
		
		$itemView = $viewPath.'.item';
		
		return $this->viewWithLastSortIndex(Timesheet::class, $viewPath.'.list', compact('list', 'itemView'), '_sort', ['x-region-commands' => $regionCommands, 'x-eventstypes' => $eventsTypes]);
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
	
	
	
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function orders_counts_stat(Request $request, Settings $settingsService) {
		[
			'period_id'	=> $periodId,
			'views'		=> $viewPath,
		] = $request->validate([
			'views'		=> 'required|string',
			'period_id'	=> 'required|numeric',
		]);
		
		$periodTitle = TimesheetPeriod::find($periodId)->title;
		$map = [];
		
		$timezones = $settingsService->get('timezones')->pluck('region', 'id')->toArray();
		$map['commands'] = Command::all()?->mapWithKeys(function($row) use($timezones) {
			$row['region'] = (int)$timezones[$row['region_id']]; // здесь region - это EU US  и т.д. а $row['region_id'] - это  CET CEST EDT и т.д.
			return [$row['id'] => $row];
		})->toArray();
		
		$map['regions'] = $settingsService->get('regions')->pluck('title', 'id')->toArray();
		
		$ordersTypes = $settingsService->get('orders_types')?->mapWithKeys(function($row) {
			return [$row['id'] => $row];
		})->toArray();
		
		
		$tsData = Timesheet::period($periodId)
			->with('orders', function($query) {
				$query->where('status', OrderStatus::ready);
			})
			->lazy();
		
		$buildData = []; 
		foreach ($tsData as $row) {
			$day = $row['datetime']?->format('Y-m-d');
			
			$ordersData = [];
			foreach ($row['orders']?->lazy() as $orderRow) {
				//if (!isset($ordersData[$orderRow['order_type']])) $ordersData[$orderRow['order_type'] ?? 0] = 0;
				//$ordersData[$orderRow['order_type'] ?? 0] += 1;
				if (!isset($buildData[$day][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'])) {
					$buildData[$day][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'] = 0;
				}
				$buildData[$day][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'] += 1;
				
				if (!isset($buildData[$day][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']])) {
					$buildData[$day][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']] = 0;
				}
				$buildData[$day][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']] += 1;
				
				if (!isset($buildData[$day][$orderRow['order_type'] ?? 0]['all'])) {
					$buildData[$day][$orderRow['order_type'] ?? 0]['all'] = 0;
				}
				$buildData[$day][$orderRow['order_type'] ?? 0]['all'] += 1;
				
				
				
				// Сумма всех дней
				if (!isset($buildData['all'][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'])) {
					$buildData['all'][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'] = 0;
				}
				$buildData['all'][$orderRow['order_type'] ?? 0]['commands'][$row['command_id'] ?? '-'] += 1;
				
				if (!isset($buildData['all'][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']])) {
					$buildData['all'][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']] = 0;
				}
				$buildData['all'][$orderRow['order_type'] ?? 0]['regions'][$map['commands'][$row['command_id'] ?? '-']['region']] += 1;
				
				if (!isset($buildData['all'][$orderRow['order_type'] ?? 0]['all'])) {
					$buildData['all'][$orderRow['order_type'] ?? 0]['all'] = 0;
				}
				$buildData['all'][$orderRow['order_type'] ?? 0]['all'] += 1;
			}
			
			ksort($buildData);
		}
		
		return response(view($viewPath.'.index', compact('buildData', 'periodTitle', 'map', 'ordersTypes')));
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