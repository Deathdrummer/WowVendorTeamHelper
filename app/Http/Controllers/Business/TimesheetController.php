<?php namespace App\Http\Controllers\business;

use App\Helpers\DdrDateTime;
use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Timesheet;
use App\Services\Settings;
use App\Traits\HasCrudController;
use Illuminate\Http\Request;

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
    public function index(Request $request) {
		[
			'views'		=> $viewPath,
			'period_id'	=> $periodId,
			'list_type'	=> $listType,
		] = $request->validate([
			'views'		=> 'required|string',
			'period_id'	=> 'required|numeric',
			'list_type'	=> 'required|string',
		]);
		
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$list = Timesheet::withCount('orders AS orders_count')
			->where('timesheet_period_id', $periodId)
			->where(function($query) use($listType) {
				if ($listType == 'actual') {
					$query->where('datetime', '>=', now());
				
				} elseif ($listType == 'past') {
					$query->where('datetime', '<', now());
				}
				
			})
			->orderBy('datetime', 'ASC')
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
		
		return $this->view($viewPath.'.init');
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
	
	
	
	
	
	
	
	
	
	//---------------------------------------------------------------------------------------
	
	
	
	
	
	/** Задать глобальные настройки
	 * @return void
	 */
	private function _buildDataFromSettings():void {
		/* $this->addSettingToGlobalData([[
			'setting'	=> 'timezones',
			'key'		=> 'id',
			'value'		=> 'timezone'
		]]); */
		
		
		$difficulties = $this->settings->get('difficulties')->mapWithKeys(function ($item, $key) {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		$eventsTypes = EventType::get()->mapWithKeys(function ($item, $key) use($difficulties) {
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
		
		$this->data['events_types'] = $eventsTypes;
		
		$commands = Command::get()->mapWithKeys(function ($item, $key)  {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		
		$this->data['commands'] = $commands;
	}
	
	
	
	
}