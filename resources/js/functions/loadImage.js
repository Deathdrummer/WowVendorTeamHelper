window.loadImage = async (fileName) => {
	
	const path = 'resources/images/'+fileName;
	
	if (isDev) {
		const resource = new URL('/'+path, import.meta.url).href;
		return resource;
	} else {
		console.log(45456);
		const filePath = await getManifest(path);
		
		return filePath;	
	}
}



$.errorLoadingImage = async (img, src = 'none_img.png') => {
	const resource = await loadImage(src);
	img.src = resource;
}