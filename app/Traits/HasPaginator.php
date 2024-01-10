<?php namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait HasPaginator {
	
	// трейту передать eloquent запрос без получения, то есть без get() all() или first()
	
	
	/**
	 * @param QueryBuilder $query
	 * @return 
	 */
	public function paginate($query = null, $currentPage, $perPage, string $fields = '*') {
		if (!$query) return false;
		$countAll = $query->count();
		
		$list = match(true) {
			$fields == '*'	=> $query->forPage($currentPage, $perPage)->get(),
			$fields != '*'	=> $query->select(preg_split('/[\s,|]+/', $fields))->forPage($currentPage, $perPage)->get(),
		};
		
		$pagination = new LengthAwarePaginator(
			$list,
			$countAll,
			$perPage,
			$currentPage,
		);
		
		if (!$allData = $pagination->toArray()) return false;
		
		return collect([
			'current_page'	=> $allData['current_page'], // Текущая страница
			'data'			=> $allData['data'], // данные (список)
			'first_item'	=> $allData['from'], // глобальный порядковый номер первого элемента списка в рамках страницы
			'last_item'		=> $allData['to'], // // глобальный порядковый номер последнего элемента списка в рамках страницы
			'last_page'		=> $allData['last_page'], // номер последней страницы
			'per_page'		=> $allData['per_page'], // кол-во записей в одной странице
			'total'			=> $allData['total'] // общее количество всех записей
		]);
	}
	
	
}