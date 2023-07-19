<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model {
    use HasFactory/*, Collectionable, Dateable, Settingable, Filterable */;
	
	
	
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'events_types';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = false;
	
	
	
	protected $fillable = [
		'title',
		'difficult_id',
		'_sort',
    ];
	
	
	
	
	
	
	/**
     * Информация 
     */
	// public function difficult() {
	// 	return $this->hasOne(ContractInfo::class, 'contract_id', 'id');
	// }
	
	
	
	
	
	
	
}
