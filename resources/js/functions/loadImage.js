window.loadImage = async (fileName) => {
	//if (isDev) {
		const url = '../../images/'+fileName;
		const resource = new URL(url, import.meta.url).href;
		return resource;
	//}
	
	
	
		
}



$.errorLoadingImage = async (img, src = 'none_img.png') => {
	const resource = await loadImage(src);
	img.src = resource;
}