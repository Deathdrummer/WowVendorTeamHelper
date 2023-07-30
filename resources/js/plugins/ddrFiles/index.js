import DdrFiles from './ddrFiles';



$.fn.ddrFiles = function(method = null, ...params) {
	if (!method) {
		if (isDev) console.error('ddrFiles -> не указан метод!');
		return false;
	}
	
	const hasMethod = [ 'choose', 'drop', 'export'].includes(method);
	
	if (!hasMethod) throw new Error(`ddrFiles -> такого метода «${method}» нет!`);
	
	const files = ref({});
	
	const methods = new DdrFiles(this, files);
	if (method.includes(':')) method = method.replace(':', '_');
		
	methods[method](...params);
	
	return methodsObj(files);
}






/*	Комбинирование методов choose и drop
		- params
			- chooseSelector: селектор открытия кна диалога
			- dropSelector: селектор drop - области бросания файлов 
			- multiple: множественный выбор
			- dragover: событие при наведении на область drop
			- dragleave: событие при уходе из области drop
			- drop: событие бросания файлов в область drop
			- init: перед инициализацией загрузки файлов
			- preload: маркировка ключем key блоков под миниатюры картинок или иконки файлов
			- callback: файл загружен 
			- fail: ошибка загрузки
*/
$.ddrFiles = function(params = {}) {
	if (!params) {
		if (isDev) console.error('$.ddrFiles -> не переданы параметры!');
		return false;
	}
	
	const {chooseSelector, dropSelector} = _.pick(params, ['chooseSelector', 'dropSelector']);
	const chooseParams = _.pick(params, ['multiple', 'init', 'preload', 'callback', 'done', 'fail']);
	const dropParams = _.pick(params, ['dragover', 'dragleave', 'drop', 'init', 'preload', 'callback', 'done', 'fail']);
	
	const files = ref({});
	
	new DdrFiles(document.querySelector(chooseSelector), files).choose(chooseParams);
	new DdrFiles(document.querySelector(dropSelector), files).drop(dropParams);
	
	
	return methodsObj(files);
}





/* методы
	- getFiles: получить все выбранне файлы
	- removeFile: удалить файл(ы) по ключу
*/
const methodsObj = function(files) {
	return {
		getFiles() {
			return files.value;
		},
		removeFile(key = null, count = 1) {
			if (_.isNull(key)) return false;
			if (files.value[key] !== undefined) delete files.value[key];
		},
	};
}