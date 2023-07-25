<?php namespace App\Models;

use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Models\Traits\Collectionable;
use App\Models\Traits\Dateable;
use App\Models\Traits\Filterable;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model {
    use HasFactory, Collectionable, Dateable, Settingable, Filterable;
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'orders';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = true;
	
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = ['date_msc'];
	
	
	protected $fillable = [
		'raw_data',
		'date',
		'order',
		'server_name',
		'link',
		'price',
		'timezone_id',
		'status',
		'created_at',
		'updated_at',
    ];

	
	
	
	
	
	
	
	public function comments(): HasMany {
        return $this->hasMany(OrderComment::class);
    }
	
	
	public function lastComment(): HasOne {
        return $this->hasOne(OrderComment::class);
    }
	
	

	
	
	
	
	
	/**
     * Добавить к выдаче время по МСК.
     *
     * @return Carbon|null
     */
    public function getDateMscAttribute():Carbon|null {
		$timezoneId = $this->attributes['timezone_id'] ?? null;
		$timestamp = $this->attributes['date'] ?? null;
		
		if (!$timezoneId || !$timestamp) return null;
		
		$timezones = $this->getSettings('timezones', 'id', 'shift');
		$shift = (-1 * (int)$timezones[$timezoneId]);
		
		return DdrDateTime::shift($timestamp, $shift);
		
		/* return match(true) {
			is_object($date)	=> DdrDateTime::shift($date, $shift),
			is_string($date)	=> DdrDateTime::buildTimestamp($date, ['shift' => $shift])
		}; */
    }
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return string
	 */
	/* public function getStatusAttribute():string {
		$stat = $this->attributes['status'] ?? 0;
		
	} */
	
	
	
			
	
	
	
	
	/* 
	Статусы
		новый new: 0
		ожидание wait: -1
		отмененcancel: -2
		готов ready: 1 
		доп. ран doprun: 2
	*/
	/**
     * Вывести с определенным статусом
     * @param $stat - new wait cancel ready doprun
     * @return Carbon|null
     */
	public function scopeStatus($query, $stat) {
		$status = OrderStatus::fromKey($stat);
		return $query->where('status', $status);
	}
	
	
	
	
	/**
     * Вывести не привязанные к событию заказы
     * @param $stat - new wait cancel ready doprun
     * @return Carbon|null
     */
	public function scopeNotTied($query) {
		return $query->whereIn('orders.id', function ($builder) {
            $builder->select('timesheet_order.order_id')->from('timesheet_order');
        }, not: true);
	}
	
	
	
	
	
	
}