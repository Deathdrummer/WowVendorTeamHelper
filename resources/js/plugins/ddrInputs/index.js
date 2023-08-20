import DdrInputs from './ddrInputs';


/*
	Работа с инпутами (кроме файлов)
	
	методы
		- clear: очистить инпуты, убрать ошибки
		- error: добавить ошибку
		- disable: запретить
		- enable: разрешить
		- value: задать значение
		- checked: пометить галочкой
		- selected: выбрать пункт вып. списка
		- setOptions: добавить пункты вып. списка
		- change: событие изменения поля или полей
		- addClass: добавить класс к обертке инпута
		- removeClass: убрать класс у обертки инпута
		- state: комплексный метод, команды:
			- clear: убрать ошибки, иземенность
		
		
		- сделать элемент некликабельным, напрмер, чекбокс:
			$(input).ddrInputs('addClass', 'notouch');
*/


$.fn.ddrInputs = function(method = false, ...params) {
	const items = this,
		isMultiple = $(this).attr('multiple') !== undefined,
		fieldName = $(this).attr('name') || 'file',
		hasMethod = [
			'clear',
			'error',
			'disable',
			'enable',
			'hide',
			'show',
			'value',
			'checked',
			'selected',
			'setOptions',
			'change',
			'change:one',
			'state',
			'addClass',
			'removeClass'].includes(method);
		
	if (!hasMethod) throw new Error(`ddrInputs -> такого метода «${method}» нет!`);
	
	if (items.length) {
		const methods = new DdrInputs(items, method);
		
		if (method.includes(':')) method = method.replace(':', '_');
		
		methods[method](...params);
	}
}