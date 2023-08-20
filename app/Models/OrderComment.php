<?php namespace App\Models;

use App\Models\Traits\Dateable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
		return $this->hasOne(AdminUser::class, 'id', 'from_id');
	}
	
	
	
	
	/**
     * Добавить время по МСК.
     *
     * @return bool
     */
    public function getSelfAttribute() {
		$origin = request()->server('HTTP_ORIGIN') ?? request()->server('REQUEST_SCHEME').'://'.request()->server('SERVER_NAME');
		$fullPath = request()->server('HTTP_REFERER');
		$replaced = Str::replace($origin, '', $fullPath);
		$guard = Str::is('/admin/*', $replaced) ? 'admin' : 'site';
		
		return $this->attributes['from_id'] == auth($guard)->user()->id;
    }
	
	
	
	
	
	
}