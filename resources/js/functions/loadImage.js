window.loadImage = async (fileName) => {
	
	
	const {default: img} =  await import('../../images/'+fileName);
	
	return img;
	//import * as imgUrl from '../../images/filetypes/svg.png';
	
	//console.log(test);
	
	//return imgUrl;
	//const url = '/resources/images/'+fileName;
	//const resource = new URL(url, import.meta.url).href;
	//return resource;
}



$.errorLoadingImage = async (img, src = 'none_img.png') => {
	const resource = await loadImage(src);
	img.src = resource;
}