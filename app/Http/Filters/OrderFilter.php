<?php namespace App\Http\Filters;

use App\Http\Filters\Base\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter {
    public const STATUS = 'status';

    protected function getCallbacks(): array {
        return [
            self::STATUS => [$this, 'status'],
        ];
    }
	
	
    public function status(Builder $builder, $value) {
		$builder->status($value);
    }
	
	
	
	
}