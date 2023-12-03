<?php namespace App\Http\Filters;

use App\Http\Filters\Base\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter {
    public const STATUS = 'status';
    public const SEARCH = 'search';
    public const WAIT_TYPE = 'wait_type';

    protected function getCallbacks(): array {
        return [
            self::STATUS => [$this, 'status'],
            self::SEARCH => [$this, 'search'],
            self::WAIT_TYPE => [$this, 'wait_type'],
        ];
    }
	
	
    public function status(Builder $builder, $value) {
		$builder->status($value);
    }
	
	
	public function search(Builder $builder, $value) {
		$builder->where('order', 'LIKE', '%'.$value.'%');
	}
	
	public function wait_type(Builder $builder, $value) {
		$builder->where('wait_group', $value);
	}
	
}