<?php namespace App\Actions;

use Error;
use Gumlet\ImageResize;

class GetImageThumbFromUrlAction {
	
	# https://packagist.org/packages/gumlet/php-image-resize
	
	/**
	* Получить миниатюру картинки из URL
	* @param 
	* @param 
	* @return string
	*/
	public function __invoke($imageUrl = null, $width = null, $height = null) {
		if (!$imageUrl || !$width ||!$height) throw new Error('GetImageThumbFromUrlAction переданы не все параметры!');
		$type = pathinfo($imageUrl, PATHINFO_EXTENSION);
		$data = file_get_contents($imageUrl);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		$thumb = new ImageResize($base64);
		$thumb->resizeToBestFit($width, $height);
		return 'data:image/' . $type . ';base64,' . base64_encode($thumb->getImageAsString());
	}
}


