<?php namespace App\Models;

use App\Models\Traits\Collectionable;
use App\Models\Traits\Dateable;
use App\Models\Traits\Filterable;
use App\Traits\Settingable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfirmedOrder extends Model {
    use HasFactory, Collectionable, Dateable, Settingable, Filterable;
	
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'confirmed_orders';
	
	
	/**
     * учитывать временные поля created_at и updated_at
	 *
     * @var string
     */
	public $timestamps = false;
	
	
	/**
     * Аксессоры, добавляемые к массиву модели.
     *
     * @var array
     */
    protected $appends = [];
	
	
	/**
     * Все поля открыты для редактирования
     *
     * @var array
     */
    protected $guarded = false;
	
	
	
	
	
	
	
	
	
}
