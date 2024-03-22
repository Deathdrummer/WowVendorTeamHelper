<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Command extends Model {
    use HasFactory/*, Collectionable, Dateable, Settingable, Filterable */;
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'commands';
	
	
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
        'region_id' => 'integer',
    ];
	
	
	
	protected $fillable = [
		'title',
		'webhook',
		'color',
		'region_id',
		'_sort',
    ];
	
	
	
	
	
	
	
	
}
