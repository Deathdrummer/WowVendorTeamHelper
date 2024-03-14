<?php namespace App\Actions;

class GetScreenshotFromFhotoScreenAction {
	
	/**
	* Получить URLкартинки из страницы скриншота Фото-скрин
	* @param 
	* @param 
	* @return string
	*/
	public function __invoke($screenUrl = null) {
		if (!$screenUrl) return false;
		$content = @file_get_contents(trim($screenUrl));
		preg_match("/<img.*id='screenshot' src='(.*)'>/U", $content, $match);	
		$imgSrc = $match[1] ?? null;
		return $imgSrc;
	}
}