<?php namespace App\Models;

use App\Models\Traits\Dateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetPeriod extends Model {
    use HasFactory, Dateable;
	
	
	
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'timesheet_periods';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = true;
	
	
	
	protected $fillable = [
		'title',
		'_sort',
    ];
	
	
	
	
	
	public function timesheet_items() {
    	return $this->hasMany(Timesheet::class, 'timesheet_period_id', 'id');
	}
	
	
	
	
}
