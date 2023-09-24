<?php namespace App\Models;

use App\Models\Traits\Dateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRawDataHistory extends Model {
    use HasFactory, Dateable;
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'order_raw_data_history';
	
	
	const UPDATED_AT = null;
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = [];
	
	
	protected $guarded = false;
	
	
	/**
     * Атрибуты, которые должны быть типизированы. (Конвертация полей при добавлении и получении)
	 *
     * @var array
     */
	protected $casts = [
        'data' => 'string',
    ];
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function author() {
		return $this->hasOne(User::class, 'id', 'from_id');
	}
	
	/**
	 * @param 
	 * @return 
	 */
	public function adminauthor() {
		return $this->hasOne(AdminUser::class, 'id', 'from_id');
	}
	
	
	
	
	/**
     * Свой ли коментарий
     *
     * @return bool
     */
    public function getSelfAttribute() {
		$guard = getGuard();
		return $this->attributes['from_id'] == auth($guard)->user()->id;
    }
	
	
}