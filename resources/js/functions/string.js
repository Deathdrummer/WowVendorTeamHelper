window.strpos = function(haystack, needle, offset) {
	var i = (haystack+'').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}





window.pregSplit = function(str = null, separator = null) {
	if (!_.isString(str)) return;
	
	const splitData = str.split(/\s*[,|]\s*|\s*[;]\s*|\s+/);
	
	return splitData.map((item) => {
		return _clearData(item);
	});
	
	function _clearData(strItem = null) {
		if (_.isNull(strItem)) return strItem;
		strItem = strItem?.trim();
		return isInt(strItem) ? parseInt(strItem) : (isFloat(strItem) ? parseFloat(strItem) : strItem);
	}
}






window.ddrSplit = function(string = null, ...separators) {
	if (_.isNull(string)) throw new Error('ddrSplit ошибка! Не передана строка!');
	if (!_.isString(string)) throw new Error('ddrSplit ошибка! Первый аргумент не является строкой!');
	let seps = [...separators];
	if (seps.length == 0) throw new Error('ddrSplit ошибка! Не переданы разделители!');
	
	function splitRecursive(str, iter = 0) {
		if (iter + 1 > seps.length) {
			return _clearData(str);
		}
		
		let res = _runRegSplit(str, seps[iter++]);
		
		if (res.length == 1) {
			return _clearData(res[0]);
		} 
		
		return res.map(function(s, k) {
			return splitRecursive(s, iter);
		});
	}
	
	return splitRecursive(string);
	
	
	
	function _runRegSplit(str, separator = null) {
		separator = _.isArray(separator) ? separator.join('|') : separator;
		let regex = new RegExp(`\\s*[${separator}]\\s*`);
		return str.split(regex);
	}
	
	function _clearData(strItem = null) {
		if (_.isNull(strItem)) return strItem;
		strItem = strItem?.trim();
		return isInt(strItem) ? parseInt(strItem) : (isFloat(strItem) ? parseFloat(strItem) : strItem);
	}
}











window.wordCase = function(count = null, variants = null) {
	if (_.isNull(count) || _.isNull(variants)) return;
	if (!_.isArray(variants)) variants = pregSplit(variants);
	count = ''+count;

	if (['11', '12', '13', '14'].indexOf(count) != -1 || ['5', '6', '7', '8', '9', '0'].indexOf(count.substr(-1)) != -1) return variants[2];
	else if (['2', '3', '4'].indexOf(count.substr(-1)) != -1 ) return variants[1];
	else if (count.substr(-1) == '1') return variants[0];
}






// Хэш сторка
window.ddrHash = function(str, seed = 0) {
	let h1 = 0xdeadbeef ^ seed, h2 = 0x41c6ce57 ^ seed;
	for (let i = 0, ch; i < str.length; i++) {
		ch = str.charCodeAt(i);
		h1 = Math.imul(h1 ^ ch, 2654435761);
		h2 = Math.imul(h2 ^ ch, 1597334677);
	}
	h1 = Math.imul(h1 ^ (h1>>>16), 2246822507) ^ Math.imul(h2 ^ (h2>>>13), 3266489909);
	h2 = Math.imul(h2 ^ (h2>>>16), 2246822507) ^ Math.imul(h1 ^ (h1>>>13), 3266489909);
	return 4294967296 * (2097151 & h2) + (h1>>>0);
};





/*
	добавить ноль к числу
		- число
		- общее кол-во цифр у числа, с учетом самого переданного числа
*/
window.addZero = function(value = null, numLength = 2) {
	return (''+value).padStart(numLength, '0')
}


/*
	Разделяет название файла на само название и расширение.
	возвращает:
		- 1: название
		- 2: расширение
	Третий аргумент: обрезает название до заданного количества символов
*/
window.getFileName = function(fileName = null, nameOrExt = null, nameLimit) {
	if (!fileName) return null;
	let fn = typeof fileName === 'object' ? fileName.name.split('.') : fileName.split('.');
	
	if (fn.length == 1) return nameOrExt == null ? [null, null] : null;
	
	let e = fn.pop(),
		n = fn.join('.');
	
	if (!nameOrExt) return [n, e];
	else if (nameOrExt == 1) return (nameLimit != undefined && isInt(nameLimit) && n.length > nameLimit) ? n.substr(0, nameLimit) : n;
	else if (nameOrExt == 2) return e;
}



window.isFile = function(file = null) {
	let ext = getFileName(file, 2);
	return !(_.isNull(ext) && file.type == '');
}


window.isImgFile = function(fileOrExt = null) {
	let ext;
	if (fileOrExt instanceof File) {
		ext = getFileName(fileOrExt, 2);
	} else {
		ext = fileOrExt;
	}
	const extensios = ['png', 'apng', 'jpeg', 'jpg', 'gif', 'bmp', 'webp'];
	return extensios.includes(ext);
}



/*
	Генерация рандомного числа
		- минимальное число
		- максимальное число
*/
window.random = function(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}


/*
	Генерация кода
		- маска: l - буква с нижним регистром, L - буква с верхним регистром, n - цифра
*/
window.generateCode = function(mask) {
	var letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
	var code = '';
	for(var x = 0; x < mask.length; x++) {
		if (mask.substr(x, 1) == 'l') code += letters[random(0,25)];
		else if (mask.substr(x, 1) == 'L') code += letters[random(0,25)].toUpperCase();
		else if (mask.substr(x, 1) == 'n') code += random(0,9);
	}
	return code;
}




/*
	Аналог функции translit из helpers.php
*/
window.translit = function(str, params) {
	let {slug, lower} = _.assign({
		slug: false,
		lower: false
	}, params);
	
	
	let converter = {
		'а': 'a',    'б': 'b',    'в': 'v',    'г': 'g',    'д': 'd',
		'е': 'e',    'ё': 'e',    'ж': 'zh',   'з': 'z',    'и': 'i',
		'й': 'y',    'к': 'k',    'л': 'l',    'м': 'm',    'н': 'n',
		'о': 'o',    'п': 'p',    'р': 'r',    'с': 's',    'т': 't',
		'у': 'u',    'ф': 'f',    'х': 'h',    'ц': 'c',    'ч': 'ch',
		'ш': 'sh',   'щ': 'sch',  'ь': '',     'ы': 'y',    'ъ': '',
		'э': 'e',    'ю': 'yu',   'я': 'ya',
		
		'А': 'A',    'Б': 'B',    'В': 'V',    'Г': 'G',    'Д': 'D',
		'Е': 'E',    'Ё': 'E',    'Ж': 'Zh',   'З': 'Z',    'И': 'I',
		'Й': 'Y',    'К': 'K',    'Л': 'L',    'М': 'M',    'Н': 'N',
		'О': 'O',    'П': 'P',    'Р': 'R',    'С': 'S',    'Т': 'T',
		'У': 'U',    'Ф': 'F',    'Х': 'H',    'Ц': 'C',    'Ч': 'Ch',
		'Ш': 'Sh',   'Щ': 'Sch',  'Ь': '',     'Ы': 'Y',    'Ъ': '',
		'Э': 'E',    'Ю': 'Yu',   'Я': 'Ya',
	};
	
	let strtrData = strtr(str, converter);
	if (slug) strtrData = strtrData.replaceAll(/[_\s]/g, '-');
	if (lower) strtrData = strtrData.toLowerCase();
	return strtrData;
}






















