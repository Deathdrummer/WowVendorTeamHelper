/*
	Поиск в массиве объектов по значению ключа
		- массив объектов
		- поле, по которому искать
		- значение, которое искать
		возвращает индекс объекта массива
*/
window.searchInObject = function(arrObj, field, value) {
	let objIndex = arrObj.findIndex(function(element, index) {
		if (element[field] == value) return index;
	});
	return objIndex;
}






window.reversedObj = (obj = null) => {
	if (!obj) return false;
	return Object.fromEntries(
		Object.entries(obj).reverse()
	);
}




window.count = function(items) {
	return _.isObject(items) ? Object.values(items)?.length : items?.length;
}
