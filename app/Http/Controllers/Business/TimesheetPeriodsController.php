<?php namespace App\Http\Controllers\business;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\EventType;
use App\Models\Timesheet;
use App\Models\TimesheetPeriod;
use App\Services\Settings;
use App\Traits\HasCrudController;
use Illuminate\Http\Request;

class TimesheetPeriodsController extends Controller {
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
		] = $request->validate([
			'views'		=> 'required|string',
		]);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$list = TimesheetPeriod::withCount(['timesheet_items'])->orderBy('_sort', 'DESC')->get();
		
		//$this->_buildDataFromSettings();
		
		$itemView = $viewPath.'.item';
		
		return $this->viewWithLastSortIndex(TimesheetPeriod::class, $viewPath.'.list', compact('list', 'itemView'), '_sort');
    }
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function last_periods(Request $request) {
		[
			'views'		=> $viewPath,
		] = $request->validate([
			'views'		=> 'required|string',
		]);
		
		if (!$viewPath) return response()->json(['no_view' => true]);
		
		$list = TimesheetPeriod::withCount(['timesheet_items'])
			->orderBy('_sort', 'DESC')
			->limit(5)
			->get();
		
		return $this->view($viewPath.'.last_periods', compact('list'));
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
		
		//$this->_buildDataFromSettings();
		
		return $this->view($viewPath.'.new', ['index' => $newItemIndex]);
    }
	
	
	
	
	
	
	
	
	/**
     * Создание ресурса и показ записи
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_show(Request $request) {
		$viewPath = $request->input('views');
		if (!$viewPath) return response()->json(['no_view' => true]);
		if (!$item = $this->_storeRequest($request)) return response()->json(false);
		
		
		//$this->_buildDataFromSettings();
		return $this->view($viewPath.'.item', $item);
    }
	
	
	
	private function _storeRequest($request = null) {
		if (!$request) return false;
		
		$validFields = $request->validate([
			'title'		=> 'required|string',
			'_sort'		=> 'exclude|regex:/[0-9]+/',
		]);
		
		if (!isset($request['_sort'])) {
			$maxSort = TimesheetPeriod::max('_sort');
			$validFields['_sort'] = $maxSort + 1;
		} else {
			$validFields['_sort'] = $request['_sort'];
		}
		
		return TimesheetPeriod::create($validFields)->withCount(['timesheet_items'])->first();
	}
	
	

	
    /**
     * Удаление записи
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(?int $id = null) {
		if (!$id) return response()->json(false);
		$stat = TimesheetPeriod::destroy($id);
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
    		//logger($item['difficult_id']);
			return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
		})->toArray();
		
		$this->data['events_types'] = $eventsTypes;
		
		$commands = Command::get()->mapWithKeys(function ($item, $key)  {
    		return [$item['id'] => $item['title']];
		})->toArray();
		
		
		$this->data['commands'] = $commands;
	}
	
	
	
	
}