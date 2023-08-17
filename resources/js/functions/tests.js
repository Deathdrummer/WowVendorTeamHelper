window.isNumeric = function(num) {
	return !_.isNaN(num) && !_.isBoolean(num) && !_.isString(num) && !_.isNull(num);
}




window.isHover = (selector = null) => {
	if (!selector) return false;
	if (_.isArray(selector)) selector = selector.join(', ')
	return !!$(selector).filter(function() {
		return $(this).is(":hover"); 
	}).length;
}




/*
	Является ли строка null
	- строка
*/
window.isNull = function(str) {
	return str === null;
}


/*
	Является ли строка json
	- строка
*/
window.isJson = function(str = null) {
	if (str == undefined || typeof str == 'undefined') return false;
	
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}


/*
	Является ли строка целым числом
*/
window.isInt = function(n) {
	if (n == undefined || typeof n == 'undefined') return false;
	if (typeof n != 'string') return Number(n) === n && n % 1 === 0;
	return Number(n)+'' === n;
}


/*
	Является ли строка числом с плавающей точкой
*/
window.isFloat = function(n) {
	if (n == undefined || typeof n == 'undefined') return false;
	if (typeof n != 'string') return Number(n) === n && n % 1 !== 0;
	return Number(n)+'' === n && Number(n) % 1 !== 0;
}


/*
	проверить наличие элемента в массиве или объекте
		- массив или объект
		- искомый элемент
		- является ли ключем искомый элемент

	ВНИМАНИЕ!!! Может возвращать 0 - это найденный индекс
*/
window.hasIn = function(data, elem, isKey) {
	if (elem == undefined || data == undefined || data.length == 0) return false;
	var findKey;
	if (isKey != undefined && isKey == true) {
		var keysData = Object.keys(data);
		findKey = keysData.indexOf(elem);

		if (findKey != -1) {
			return data[keysData[findKey]];
		}
		return false;
	}

	findKey = data.indexOf(elem);
	return (findKey != -1 ? findKey : false);
}







/*
	Проверка существования файла
		- путь до файла
*/
window.urlExists = function(url) {
	var http = new XMLHttpRequest();
	http.open('HEAD', url, false);
	http.send();
	return http.status != 404;
}




















