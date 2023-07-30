
/*
	функция работы с localStorage
		- ключ
		- значение (если значение не указано - то ведется запрос данных по ключу, если значение - FALSE - то данные по ключу удаляются)
*/
window.ddrStore = function(key, value) {
	if (!key || typeof key != 'string') return false;
	if (value === false) {
		localStorage.removeItem(key);
	} else if (value !== undefined) {
		if (typeof value == 'object') value = JSON.stringify(value);
		localStorage.setItem(key, value);
	} else {
		let getValue = localStorage.getItem(key);
		if (isJson(getValue)) getValue = JSON.parse(getValue);
		return getValue !== null ? getValue : null;
	}
}







/*
	массив с накоплением каких-либо данных
		- сам массив
		- значение, которое нужно добавить или удалить
		- флаг добавить или удалить true \ false
	Возвращает измененный массив
*/
window.storeArray = function(storeArr = [], value, stat = true) {
	if (stat) {
		if (_.isArray(value)) {
			$.each(value, (item) => {
				storeArr.push(value);
			});
		} else {
			storeArr.push(value);
		}
	} else {
		_.pull(storeArr, value);
	}
	return storeArr;
}