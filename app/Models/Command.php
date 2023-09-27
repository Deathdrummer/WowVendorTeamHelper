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
	
	
	
	protected $fillable = [
		'title',
		'color',
		'region_id',
		'_sort',
    ];
	
	
	
	
	
	
	
	
}
