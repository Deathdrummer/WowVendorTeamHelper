/*
	Добавление атрибута
		- название атрибута
		- значение
*/
$.fn.setAttrib = function(attr, value) {
	if (attr == undefined) return false;
	if ($(this).length == 0) return false;
	if (_.isNumber(attr)) throw new Error('setAttrib -> ошибка! Передаваемы атрибут не может быть числом!');
	$(this).attr(String(attr), (value || ''));
	$(this)[0].setAttribute(String(attr), (value || ''));
};

/*
	Удаление атрибута
		- название атрибута
*/
$.fn.removeAttrib = function(attr) {
	if (attr == undefined) return false;
	if ($(this).length == 0) return false;
	$(this).prop(attr, false);
	$(this).removeAttr(attr);
	$(this)[0].removeAttribute(attr);
};






/*
	Проверка наличия атрибута
		- название атрибута
*/
$.fn.hasAttr = function(a) {
	var attr = $(this).attr(a);
	return typeof attr !== typeof undefined && attr !== false;
}

$.fn.hasAttrib = function(a) {
	var attr = $(this).attr(a);
	return typeof attr !== typeof undefined && attr !== false;
}








window.attribData = function(e, d) {
	var data, attrs, at = '';
	if (thisDevice == 'mobile' && !isIos) {
		attrs = e.changedTouches != undefined ? e.changedTouches[0].target.attributes : false;
	} else {
		attrs = e.target.attributes || false;
	}

	if (attrs.length) {
		$.each(attrs, function(k, a) {
			at += ' '+a.name;
		});
	}

	data = {
		attributes: (at && typeof at == 'string') ? at.trim().split(' ') : false
	};
	
	if (d != undefined && d.attribute) {
		if (data.attributes) {
			var fStat = false;
			if (typeof d.attribute == 'object') {
				$.each(d.attribute, function(k, attr) {
					if (data.attributes.indexOf(attr) != -1) fStat = true;
				});
				return fStat;
			} else return (data.attributes.indexOf(d.attribute) != -1);
		} else return false;
	}

	return data?.attributes;
}


