<?php namespace App\Models;

use App\Enums\OrderStatus;
use App\Helpers\DdrDateTime;
use App\Models\Traits\Collectionable;
use App\Models\Traits\Dateable;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasEvents;
use App\Traits\Settingable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Order extends Model {
    use HasFactory, Collectionable, Dateable, Settingable, Filterable, HasEvents;
	
	
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
    protected $appends = ['date_msc', 'order_type_title'];
	
	
	protected $fillable = [
		'raw_data',
		'date',
		'date_add',
		'order',
		'order_type',
		'ot_changed',
		'manually',
		'server_name',
		'link',
		'price',
		'timezone_id',
		'status',
		'created_at',
		'updated_at',
    ];
	
	
	
	
	
	
	public function comments():HasMany {
        return $this->hasMany(OrderComment::class);
    }
	
	
	public function lastComment():HasOne {
        return $this->hasOne(OrderComment::class)->latestOfMany();
    }
	
	
	public function rawDataHistory():HasMany {
        return $this->hasMany(OrderRawDataHistory::class, 'order_id', 'id');
    }
	
	
	
	public function timesheets():BelongsToMany {
		return $this->belongsToMany(
			Timesheet::class,
			'timesheet_order',
			'order_id',
			'timesheet_id',
			'id',
			'id')
			->as('pivot')
			->withPivot('doprun');
	}
	
	
	
	public function mainTimesheet():hasOneThrough {
		return $this->hasOneThrough(
            Timesheet::class,
            TimesheetOrder::class,
            'order_id', // Внешний ключ в таблице `timesheet_order` ...
            'id', // Внешний ключ в таблице `timesheets` ...
            'id', // Локальный ключ в таблице `orders` ...
            'timesheet_id' // Локальный ключ в таблице `timesheet_order` ...
        )->where('cloned', null);
	}
	
	
	
	
	
	
	
	public function timesheet_to_confirm():BelongsToMany {
		return $this->belongsToMany(
			Timesheet::class,
		 	'confirmed_orders',
			'order_id',
			'timesheet_id',
			'id',
			'id')
			->as('pivot')
			->withPivot('from_id', 'confirm', 'date_add', 'date_confirm');
	}

	public function has_confirm_orders():BelongsToMany {
		return $this->belongsToMany(
			Timesheet::class,
		 	'confirmed_orders',
			'order_id',
			'timesheet_id',
			'id',
			'id')
			->as('pivot')
			->withPivot('confirm')/* 
			->wherePivot('confirm', 1) */;
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
    }
	
	
	
	/**
	* Добавить к выдаче тип заказа
	* @param 
	* @return 
	*/
	public function getOrderTypeTitleAttribute() {
		$orderType = $this->attributes['order_type'] ?? null;
		$orderTypesData = $this->getSettings('orders_types', 'id', 'title', ['id' => $orderType]);
		return $orderTypesData[$orderType] ?? null;
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
		новый 		new: 	0
		ожидание 	wait: 	-1
		отменен 	cancel: -2
		некрота 	necro: 	-3
		готов 		ready: 	1 
		доп. ран 	doprun:	2
	*/
	/**
     * Вывести с определенным статусом
     * @param $stat - new wait cancel ready doprun
     */
	public function scopeStatus($query, $stat) {
		$status = OrderStatus::fromKey($stat);
		return $query->where('status', $status);
	}
	
	
	
	
	/**
     * Вывести не привязанные к событию заказы
     */
	public function scopeNotTied($query) {
		return $query->whereIn('orders.id', function ($builder) {
            $builder->select('timesheet_order.order_id')->from('timesheet_order');
        }, not: true);
	}
	
	
	/**
     * Вывести привязанные к событию заказы
     */
	public function scopeTied($query) {
		return $query->whereIn('orders.id', function ($builder) {
            $builder->select('timesheet_order.order_id')->from('timesheet_order');
        }, not: false);
	}
	
	
	
	/**
     * Вывести неподтвержденные заказы
     */
	public function scopeNotConfirmed($query) {
		return $query->whereIn('orders.id', function ($builder) {
            $builder->select('confirmed_orders.order_id')->from('confirmed_orders');
        }, not: true);
	}
	
	
	
	/**
     * Вывести подтвержденные заказы
     */
	public function scopeConfirmed($query, $type) {
		return $query->whereIn('orders.id', function ($builder) use($type) {
            $builder->select('confirmed_orders.order_id')
				->from('confirmed_orders')
				->when($type, function($q) use($type) {
					$confirm = match($type) {
						'actual'	=> 0,
						'past'		=> 1,
						default		=> 0,
					};
					
					$q->where('confirmed_orders.confirm', $confirm);
				});
        }, not: false)->when($type == 'past', function($tq) {
			$limit = $this->getSettings('orders.confirm_past_limit') ?? 70;
			if ($limit) $tq->limit($limit);
		});
	}
	
	
	
	
	
}