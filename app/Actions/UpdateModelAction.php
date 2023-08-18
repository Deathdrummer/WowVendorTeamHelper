<?php namespace App\Actions;

use Illuminate\Support\Facades\App;

class UpdateModelAction {
	/**
	* 
	* @param 
	* @return 
	*/
	public function __invoke($modelClass, $id, $data) {
		$model = App::make($modelClass);
		$currendModel = $model->find($id);
		$currendModel->fill($data);
		$res = $currendModel->save();
		return $res;
	}
}