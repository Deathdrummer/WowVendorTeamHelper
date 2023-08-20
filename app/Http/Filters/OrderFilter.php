<?php namespace App\Http\Filters;

use App\Http\Filters\Base\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter {
    public const STATUS = 'status';
    public const SEARCH = 'search';

    protected function getCallbacks(): array {
        return [
            self::STATUS => [$this, 'status'],
            self::SEARCH => [$this, 'search'],
        ];
    }
	
	
    public function status(Builder $builder, $value) {
		$builder->status($value);
    }
	
	
	 public function search(Builder $builder, $value) {
		$builder->where('order', 'LIKE', '%'.$value.'%');
    }
	
	
	
	
}