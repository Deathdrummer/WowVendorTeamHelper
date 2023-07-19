<?php namespace App\Models\Traits;

use App\Helpers\DdrDateTime;

trait Dateable {
	
    public function getCreatedAtAttribute($timestamp = null) {
		return DdrDateTime::shift($timestamp, 'TZ');
    }
	
	public function getUpdatedAtAttribute($timestamp = null) {
		return DdrDateTime::shift($timestamp, 'TZ');
    }
}