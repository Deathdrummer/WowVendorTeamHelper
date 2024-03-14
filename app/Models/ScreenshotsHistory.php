<?php namespace App\Models;

use App\Helpers\DdrDateTime;
use App\Models\Traits\Collectionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenshotsHistory extends Model {
   use HasFactory, Collectionable;
	
	/**
     * Обработка события перед созданием записи.
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function (ScreenshotsHistory $screenshotsHistory) {
            $screenshotsHistory->date_add = DdrDateTime::now();
        });
    }
	
	
	
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'screenshots_history';
	
	
	
	/**
     * Поля разрешенные для редактирования
	 *
     * @var array
     */
	protected $guarded = false;
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = false;
	
	
	/**
     * Атрибуты, которые должны быть типизированы. (Конвертация полей при добавлении и получении)
     *
     * @var array
     */
    protected $casts = [
        'stat' => 'array',
    ];
	
	
	
	
	/**
	 * @param string  $value
	 * @return 
	 */
	/* public function setStatAttribute($value) {
		$this->attributes['stat'] = is_array($value) ? json_encode(array_values($value)) : $value;
	} */
	
	
	/**
     * 
     *
     * @param string  $value
     * @return string
     */
    /* public function getStatAttribute($value) {
		return isJson($value) ? json_decode($value, true) : $value;
	} */
	
	
	
	
	
	
	
	
	
	/**
     * Получить данные по ID события
     * @param $stat - new wait cancel ready doprun
     * @return Carbon|null
     */
	public function scopeTimesheet($query, $timesheetId = null) {
		return $query->where('timesheet_id', $timesheetId);
	}
	
}
