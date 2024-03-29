<?php namespace App\Models;

use App\Helpers\DdrDateTime;
use App\Models\Traits\HasEvents;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
		static::created(function(Timesheet $timesheet) {
			eventLog()->timesheetCreated($timesheet);
		});
		
		static::updated(function(Timesheet $timesheet) {
			eventLog()->timesheetUpdated($timesheet);
		});
		
		static::deleted(function(Timesheet $timesheet) {
			eventLog()->timesheetRemoved($timesheet);
		});
    }
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function ordersTypes():BelongsToMany {
		return $this->belongsToMany(
			Order::class,
			'timesheet_order',
			'timesheet_id',
			'order_id',
			'id',
			'id')
			->select('order_type');
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
			->withPivot('doprun', 'cloned', 'date_add')
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
     * Получить грядущие события с нижнми смешением по времени из настройки timesheet.attach_order_events_offset_hours
     * @param $calendarDate дата календаря
     * @param $orderDate дата заказа
     * @return Builder
     */
	public function scopeFuture($query, $calendarDate = null, $orderDate = null):Builder  {
		if ((!$calendarDate && !$orderDate)) return $query->whereBetween('datetime', [now()->startOfDay(), now()->endOfDay()]);
		
		$timesheetOffsetHours = $this->getSettings('timesheet.attach_order_events_offset_hours') ?: 0;
		
		$orderDate = DdrDateTime::buildTimestamp($orderDate);
		$calendarDate = DdrDateTime::buildTimestamp($calendarDate);
		
		$orderDay = $orderDate?->day;
		$calendarDay = $calendarDate?->day;
		$nowDay = now()?->day;
		
		$dateStart = $orderDay == $calendarDay ? DdrDateTime::shift($orderDate, 'UTC')?->addHours($timesheetOffsetHours) : ($calendarDay == $nowDay ? now()?->addHours($timesheetOffsetHours) : DdrDateTime::shift($calendarDate, 'UTC'));
		
		$dateEnd = $calendarDate?->copy()?->endOfDay();
		
		return $query->whereBetween('datetime', [$dateStart, DdrDateTime::shift($dateEnd, 'UTC')]);
	}
	
	
	
	/**
     * Получить прошедшие события с верхним смешением по времени из настройки timesheet.attach_order_events_offset_hours 
     * @param $calendarDate дата календаря
     * @param $orderDate дата заказа
     * @return Builder
     */
	public function scopePast($query, $calendarDate = null, $orderDate = null):Builder {
		if (!$calendarDate && !$orderDate) return $query->where('datetime', '<', now());
		
		$timesheetOffsetHours = $this->getSettings('timesheet.attach_order_events_offset_hours') ?: 0;
		
		$orderDate = DdrDateTime::buildTimestamp($orderDate);
		$calendarDate = DdrDateTime::buildTimestamp($calendarDate);
		
		$orderDay = $orderDate?->day;
		$calendarDay = $calendarDate?->day;
		$nowDay = now()?->day;
		
		$dateEnd = $orderDay == $calendarDay ? ($calendarDay != $nowDay ? DdrDateTime::shift($calendarDate?->endOfDay(), 'UTC') : $orderDate?->addHours($timesheetOffsetHours))  : ($calendarDay == $nowDay ? now()?->addHours($timesheetOffsetHours) : DdrDateTime::shift($calendarDate?->endOfDay(), 'UTC'));
		
		$dateStart = $dateEnd?->copy()?->startOfDay();
		
		return $query->whereBetween('datetime', [DdrDateTime::shift($dateStart, 'UTC'), $dateEnd]);
	}
	
	
	
	
	/**
     * Получить 
     * @param $stat - new wait cancel ready doprun
     * @return Carbon|null
     */
	public function scopePeriod($query, $periodId = null) {
		return $query->where('timesheet_period_id', $periodId);
	}
}
