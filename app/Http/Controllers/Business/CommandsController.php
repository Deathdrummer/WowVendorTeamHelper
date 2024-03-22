<?php namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Services\Settings;
use App\Traits\HasCrudController;
use Illuminate\Http\Request;

class CommandsController extends Controller {
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
		
		$list = Command::orderBy('_sort', 'ASC')->get();
		
		$this->_buildDataFromSettings();
		
		$itemView = $viewPath.'.item';
		
		return $this->viewWithLastSortIndex(Command::class, $viewPath.'.list', compact('list', 'itemView'), '_sort');
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
		
		return $this->view($viewPath.'.new', ['index' => $newItemIndex]);
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
		$viewPath = $request->input('views');
		if (!$viewPath) return response()->json(['no_view' => true]);
		if (!$item = $this->_storeRequest($request)) return response()->json(false);
		$this->_buildDataFromSettings();
		return $this->view($viewPath.'.item', $item);
    }
	
	
	
	private function _storeRequest($request = null) {
		if (!$request) return false;
		
		$validFields = $request->validate([
			'region_id'	=> 'required|numeric|nullable',
			'title'		=> 'required|string',
			'webhook'	=> 'required|string',
			'color'		=> 'required|string',
			'_sort'		=> 'exclude|regex:/[0-9]+/',
		]);
		
		if (!isset($validFields['_sort'])) {
			$maxSort = Command::max('_sort');
			$validFields['_sort'] = $maxSort + 1;
		}
		
		return Command::create($validFields);
	}
	
	
	

    /**
     * Показ определенной записи
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $viewPath = $request->input('views');
		$data = $request->except(['views']);
		if (!$viewPath) return response()->json(['no_view' => true]);
		return $this->view($viewPath.'.item', $data);
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
			'region_id'	=> 'required|numeric|nullable',
			'title'		=> 'required|string',
			'webhook'	=> 'required|string',
			'color'		=> 'required|string',
			'views'		=> 'required|string|exclude'
		]);
		
		
		$contract = Command::find($id);
		$contract->fill($validFields);
		$contract->save();
		
		$viewPath = $request->input('views');
		$this->_buildDataFromSettings();
		

		return $this->view($viewPath.'.item', $contract);
    }
	
	
	
	
	
    /**
     * Удаление записи
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(?int $id = null) {
		if (!$id) return response()->json(false);
		$stat = Command::destroy($id);
		return response()->json($stat);
    }
	
	
	
	
	
	
	
	
	
	//---------------------------------------------------------------------------------------
	
	
	
	
	
	/** Задать глобальные настройки
	 * @return void
	 */
	private function _buildDataFromSettings():void {
		$this->addSettingToGlobalData([[
			'setting'	=> 'timezones',
			'key'		=> 'id',
			'value'		=> 'timezone'
		]]);
	}
	
	
	
	
}