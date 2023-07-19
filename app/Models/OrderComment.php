<?php namespace App\Models;

use App\Models\Traits\Dateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderComment extends Model {
    use HasFactory, Dateable;
	
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = ['self'];
	
	
	protected $fillable = [
		'order_id',
		'from_id',
		'message',
		'created_at',
		'updated_at',
    ];
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function author() {
		return $this->hasOne(AdminUser::class, 'id', 'from_id');
	}
	
	
	
	
	/**
     * Добавить время по МСК.
     *
     * @return bool
     */
    public function getSelfAttribute() {
		return $this->attributes['from_id'] == auth('admin')->user()->id;
    }
	
	
	
	
	
	
}