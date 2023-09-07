<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLoger extends Model {
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
     * Поля разрешенные для редактирования
	 *
     * @var array
     */
	protected $guarded = false;
	
	
	
}