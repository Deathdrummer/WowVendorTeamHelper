<?php namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class UpdateModelAction {
	/**
	* 
	* @param $modelClass модель: Model::class
	* @param $idOrField поле ID или указать массивом: [поле => значение]
	* @param $data данные для обновления
	* @return Model|bool|null
	*/
	public function __invoke($modelClass, $idOrField, $data, $returnModel = false):Model|bool|null {
		$model = App::make($modelClass);
		$currendModel = is_array($idOrField) ? $model->where($idOrField)->firstOrFail() : $model->find($idOrField);
		$currendModel->fill($data);
		$res = $currendModel->save();
		
		return $returnModel ? $currendModel : $res;
	}
}