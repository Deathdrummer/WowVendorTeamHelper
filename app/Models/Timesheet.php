<?php namespace App\Models;

use App\Actions\LogEventAction;
use App\Enums\LogEventsTypes;
use App\Helpers\DdrDateTime;
use App\Models\Traits\HasEvents;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function Illuminate\Events\queueable;

class Timesheet extends Model {
    use HasFactory, HasEvents, Settingable/*, Collectionable, Dateable, Filterable */;
	
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'timesheet';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = false;
	
	
	
	protected $fillable = [
		'command_id',
		'event_type_id',
		'timesheet_period_id',
		'comment',
		'datetime',
		'_sort',
    ];
	
	
	
	
	
	
    protected static function booted():void {
		$eventLog = app()->make(LogEventAction::class);
		
		static::created(function(Timesheet $timesheet) use($eventLog) {
			$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
			
			$command = Command::find($timesheet?->command_id);
			$eventsTypes = getEventsTypes();
			
			$info = [
				'id' => ['data' => $timesheet?->id ?? '-', 'title' => 'ID события'],
				'command_id' => ['data' => $command?->title ?? '-', 'title' => 'Команда'],
				'timesheet_period_id' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
				'event_type_id' => ['data' => $eventsTypes[$timesheet?->event_type_id] ?? '-', 'title' => 'Тип события'],
				'datetime' => ['data' => $timesheet?->datetime ?? '-', 'title' => 'Дата и время'],
			];
			
			$eventLog(LogEventsTypes::timesheetCreated, $info);
		});
		
		static::updated(function(Timesheet $timesheet) use($eventLog) {
			$eventsTypes = getEventsTypes();
			$buildFields = self::buildFields($timesheet, ['event_type_id' => $eventsTypes], ['datetime']);
			
			$info = [
				'id' => $buildFields('id', 'ID события'),
				'command_id' => $buildFields('command_id', function($orig, $upd) {
					$row['data'] = Command::find($orig)?->title;
					if ($upd) $row['updated'] = Command::find($upd)?->title;
					return $row;
				}, 'Команда'),
				'timesheet_period_id' => $buildFields('timesheet_period_id', function($orig) {
					return ['data' => TimesheetPeriod::find($orig)?->title];
				}, 'Период'),
				'event_type_id'	=> $buildFields('event_type_id', 'Тип события'),
				'datetime'	=> $buildFields('datetime', 'Дата и время'),
			];
			
			$eventLog(LogEventsTypes::timesheetUpdated, $info);
		});
		
		static::deleted(function(Timesheet $timesheet) use($eventLog) {
			$timesheetPeriod = TimesheetPeriod::find($timesheet?->timesheet_period_id);
			
			$command = Command::find($timesheet?->command_id);
			$eventsTypes = getEventsTypes();
			
			$info = [
				'id' => ['data' => $timesheet?->id ?? '-', 'title' => 'ID события'],
				'command_id' => ['data' => $command?->title ?? '-', 'title' => 'Команда'],
				'timesheet_period_id' => ['data' => $timesheetPeriod?->title ?? '-', 'title' => 'Период'],
				'event_type_id' => ['data' => $eventsTypes[$timesheet?->event_type_id] ?? '-', 'title' => 'Тип события'],
				'datetime' => ['data' => $timesheet?->datetime ?? '-', 'title' => 'Дата и время'],
			];
			
			$eventLog(LogEventsTypes::timesheetRemoved, $info);
		});
		
		
		
		//-----------------------------------------------------------------------------------------------
		function getEventsTypes():array {
			$difficulties = Settingable::getSettingsStatic('difficulties', 'id', 'title');
			return EventType::get()?->mapWithKeys(function ($item, $key) use($difficulties) {
				return [$item['id'] => $item['title'].'-'.$difficulties[$item['difficult_id']]];
			})->toArray();
		}
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function orders():BelongsToMany {
		return $this->belongsToMany(
			Order::class,
			'timesheet_order',
			'timesheet_id',
			'order_id',
			'id',
			'id')
			->as('pivot')
			->with('lastComment')
			->withPivot('doprun')
			->using(TimesheetOrder::class);
	}
	
	
	
	public function confirmOrders():BelongsToMany {
		return $this->belongsToMany(
			Order::class,
		 	'confirmed_orders',
			'timesheet_id',
			'order_id',
			'id',
			'id')
			->as('pivot')
			->withPivot('from_id', 'confirm', 'date_add', 'date_confirm')
			->using(TimesheetOrder::class);
	}
	
	
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function command() {
		return $this->hasOne(Command::class, 'id', 'command_id');
	}
	
	
	
	
	
	
	/**
	 * @param 
	 * @return Carbon|null
	 */
	public function getDatetimeAttribute():Carbon|null {
		if (!$datetime = $this->attributes['datetime'] ?? null) return null;
		return DdrDateTime::shift($datetime, 'TZ');
	}
	
	
	
	
	

	/**
     * Получить 
     * @param $stat - new wait cancel ready doprun
     * @return Carbon|null
     */
	public function scopeFuture($query, $date = null) {
		$dateEnd = Carbon::create($date)->addHours(23)->addMinutes(59);
		if ($date) return $query->whereBetween('datetime', [$date, $dateEnd])->where('datetime', '>=', now());
		return $query->where('datetime', '>=', now());
	}
	
	
	
}
