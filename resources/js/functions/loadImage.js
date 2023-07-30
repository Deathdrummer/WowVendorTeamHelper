window.loadImage = async (fileName) => {
	return getImageUrl(fileName);
}



$.errorLoadingImage = async (img, src = 'none_img.png') => {
	const resource = await loadImage(src);
	img.src = resource;
}