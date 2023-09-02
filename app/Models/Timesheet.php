<?php namespace App\Models;

use App\Helpers\DdrDateTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Timesheet extends Model {
    use HasFactory/*, Collectionable, Dateable, Settingable, Filterable */;
	
	
	
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
			->withPivot('doprun');
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
			->withPivot('from_id', 'confirm', 'date_add', 'date_confirm');
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
