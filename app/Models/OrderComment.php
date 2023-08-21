<?php namespace App\Models;

use App\Enums\Guards;
use App\Models\Traits\Dateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderComment extends Model {
    use HasFactory, Dateable;
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'order_comments';
	
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = ['self'];
	
	
	protected $fillable = [
		'order_id',
		'from_id',
		'user_type',
		'message',
		'created_at',
		'updated_at',
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