<?php namespace App\Models;

use App\Helpers\DdrDateTime;
use App\Models\Traits\Collectionable;
use App\Models\Traits\Dateable;
use App\Models\Traits\Filterable;
use App\Traits\Settingable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TimesheetOrder extends Pivot {
    use HasFactory, Collectionable, Dateable, Settingable, Filterable;
	
	
	/**
     * Обработка события перед созданием записи.
     *
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function ($TimesheetOrder) {
            $TimesheetOrder->date_add = DdrDateTime::now();
        });
    }
	
	
	
	
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'timesheet_order';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = false;
	
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = [];
	
	
	/**
     * Все поля открыты для редактирования
     *
     * @var array
     */
    protected $guarded = false;
	
	
	
}