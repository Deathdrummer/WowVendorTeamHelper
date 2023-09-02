<?php namespace App\Actions;

use Illuminate\Support\Facades\App;

class UpdateModelAction {
	/**
	* 
	* @param $modelClass модель: Model::class
	* @param $idOrField поле ID или указать массивом: [поле => значение]
	* @param $data данные для обновления
	* @return bool|null
	*/
	public function __invoke($modelClass, $idOrField, $data):bool|null {
		$model = App::make($modelClass);
		$currendModel = is_array($idOrField) ? $model->where($idOrField)->firstOrFail() : $model->find($idOrField);
		$currendModel->fill($data);
		$res = $currendModel->save();
		return $res;
	}
}