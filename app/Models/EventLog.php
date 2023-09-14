<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventLog extends Model {
	use HasFactory;
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'events_logs';
	
	
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
        'info' => 'array',
    ];
	
	
	
	/**
     * Поля разрешенные для редактирования
	 *
     * @var array
     */
	protected $guarded = false;
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function author():HasOne {
		return $this->HasOne(User::class, 'id', 'from_id');
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function adminauthor():HasOne {
		return $this->HasOne(AdminUser::class, 'id', 'from_id');
	}
	
	
	
	
	
	
	
	
	
}