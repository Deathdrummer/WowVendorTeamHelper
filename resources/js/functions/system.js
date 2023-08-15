window.isIos = /iPhone|iPad|iPod/i.test(navigator.userAgent);
window.tapEvent = (('ontouchstart' in window) && !isIos) ? 'tap' : 'click';

$.fn.ddrClick = function(callback = null, countClicks = 1) {
	if (!_.isFunction(callback)) return;
	
	$(this).on(tapEvent, function (e) {
		e.preventDefault();
		if (e.detail >= countClicks && e.detail < countClicks + 1) {
			callFunc(callback, this);
		}
	});
}






window.getOS = function() {
	let userAgent = window.navigator.userAgent,
		platform = window.navigator?.userAgentData?.platform || window.navigator.platform,
		macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K', 'macOS'],
		windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
		iosPlatforms = ['iPhone', 'iPad', 'iPod'],
		os = null;
	
	if (macosPlatforms.indexOf(platform) !== -1) {
		os = 'MacOS';
	} else if (iosPlatforms.indexOf(platform) !== -1) {
		os = 'iOS';
	} else if (windowsPlatforms.indexOf(platform) !== -1) {
		os = 'Windows';
	} else if (/Android/.test(userAgent)) {
		os = 'Android';
	} else if (/Linux/.test(platform)) {
		os = 'Linux';
	}

	return os;
}



	




window.getManifest = async function(path = null) {
	if (isDev) {
		console.error('getManifest -> это локальная версия сайта, тут манифеста нет!');
		return false;
	}
	/*
	if (!path) {
		if (isDev) console.error('getManifest -> не передан путь!');
		return false;
	}
	
	let savedManifest = ddrStore('manifest');
	
	
	
	if (!savedManifest) {
		try {
			const uuu = isDev ? '/public/build/manifest.json' : '';
			const {default: manifest} = await import(''));
			ddrStore('manifest', manifest);
			savedManifest = manifest;
		} catch (err) {
			throw new Error(`Unable to import manifest`)
		}
	}
	
	path = path.substr(0, 1) == '/' ? path.slice(1) : path;
	
	return savedManifest[path]?.file || null;	*/
}





window.callFunc = function(func, ...params) {
	if (!_.isFunction(func)) {
		if (isDev) console.log(`callFunc -> ${func} не является функцией!`);
		return;
	}
	if (_.isFunction(func)) return func(...params);
}







window.ref = function (data) {
	let target = {};
	let proxy = new Proxy(target, {
		get(target, prop) {
			if (prop in target) {
				if (_.isNumber(target[prop])) return Number(target[prop]);
				return target[prop];
			} else {
				return null;
			}
		}
	});
	proxy.value = data;
	return proxy;
}




/*
	shift  		shift 	shiftKey
	option 		alt  	altKey
	command  	ctrl 	metaKey \ ctrlKey
*/
window.metaKeys = function(event = null) {
	if (_.isNull(event)) throw new Error('metaKeys ошибка! Не передан event!');
	
	const {shiftKey, ctrlKey, altKey, metaKey} = event;
	
	return {
		isShiftKey: shiftKey,
		isCtrlKey: ctrlKey || metaKey,
		isCommandKey: ctrlKey || metaKey,
		isAltKey: altKey,
		isOptionKey: altKey,
		noKeys: !shiftKey && !altKey && !(ctrlKey || metaKey),
		isActiveKey(key = null) {
			if (_.isNull(event)) return false;
			
			if (_.isArray(key)) {
				if (['ctrl', 'command'].some((k) => key.indexOf(k) !== -1) && (ctrlKey || metaKey)) return true;
				if (['shift'].some((k) => key.indexOf(k) !== -1) && shiftKey) return true;
				if (['alt', 'option'].some((k) => key.indexOf(k) !== -1) && altKey) return true;
				return false;
			}
			
			if (['ctrl', 'command'].indexOf(key) !== -1 && (ctrlKey || metaKey)) return true;
			if (key == 'shift' && shiftKey) return true;
			if (['alt', 'option'].indexOf(key) !== -1 && altKey) return true;
			return false;
		},
	};
}











/*
	shift  		shift 	shiftKey
	option 		alt  	altKey
	command  	ctrl 	metaKey \ ctrlKey
*/
window.mouseClick = function(event = null) {
	if (_.isNull(event)) throw new Error('mouseClick ошибка! Не передан event!');
	
	const {which} = event;
	
	return {
		isLeftClick: which == 1,
		isRightClick: which == 3,
		isCenterClick: which == 2,
	};
}




/*
	Определение устройства: desktop или mobile
*/
window.thisDevice = 'desktop';
if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
	window.thisDevice = 'mobile';
}


window.getBrowserType = function () {
  const test = regexp => {
	return regexp.test(navigator.userAgent);
  };

  if (test(/opr\//i) || !!window.opr) {
	return 'Opera';
  } else if (test(/edg/i)) {
	return 'Microsoft Edge';
  } else if (test(/chrome|chromium|crios/i)) {
	return 'Google Chrome';
  } else if (test(/firefox|fxios/i)) {
	return 'Mozilla Firefox';
  } else if (test(/safari/i)) {
	return 'Apple Safari';
  } else if (test(/trident/i)) {
	return 'Microsoft Internet Explorer';
  } else if (test(/ucbrowser/i)) {
	return 'UC Browser';
  } else if (test(/samsungbrowser/i)) {
	return 'Samsung Browser';
  } else {
	return 'Unknown browser';
  }
}





// задать или получить CSS переменную. [Если не указывать значение - то функция вернет значение переменной ]
//	- название переменной (можно без «--»)
//	- значение
window.ddrCssVar = function(variable, value) {
	if (variable === undefined) return false;
	let v = variable.replace('--', '');
	if (value !== undefined) {
		return document.documentElement.style.setProperty('--'+v, value);
	}
	//return document.documentElement.style.getPropertyValue('--'+v);
	return getComputedStyle(document.documentElement).getPropertyValue('--'+v);
}









/*
	Массив данных брейкпоинтов, пример: {sm: 576, md: 768, lg: 992, xl: 1370}
	либо переменные: breakpointSM, breakpointMD, breakpointLG, breakpointXL
*/
window.breakpoints = {};
['SM', 'MD', 'LG', 'XL', 'XXL'].forEach(function(brName) {
	var val = parseInt($(':root').css('--breakpoint-'+brName.toLowerCase()));
	window['breakpoint'+brName] = val;
	window.breakpoints[brName.toLowerCase()] = val;
});


/*
	Задать значения переменной для каждого брейкпоинта
	brSteps({xs: ..., sm: ..., ...});
*/
window.brSteps = function(bpMap = false) {
	if (!bpMap) return false;
	let winW = $(window).width(),
		currentBpVal = null;
	
	['xs', 'sm', 'md', 'lg', 'xl', 'xxl'].forEach(function(brName) {
		if (typeof bpMap[brName] !== 'undefined') currentBpVal = bpMap[brName];
		bpMap[brName] = currentBpVal;
	});
	
	if (winW < breakpoints['sm']) return bpMap['xs'];
	else if (winW >= breakpoints['sm'] && winW < breakpoints['md']) return bpMap['sm'];
	else if (winW >= breakpoints['md'] && winW < breakpoints['lg']) return bpMap['md'];
	else if (winW >= breakpoints['lg'] && winW < breakpoints['xl']) return bpMap['lg'];
	else if (winW >= breakpoints['xl'] && winW < breakpoints['xxl']) return bpMap['xl'];
	else if (winW >= breakpoints['xxl']) return  bpMap['xxl'];
}



/*
	Получить текущий брейкпоинт
		- объект значений. Пример: {xs: значение 1, sm: значение 2, ...и т.д.} - вернет значение, соответствующее текущему брейпоинту
*/
window.getCurrentBreakPoint = function(values = false) {
	let bps = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'],
		winW = $(document).width(),
		returnValue;

	bps.forEach(function(bp, k) {
		let bpWidth = breakpoints[bp] || 0,
			nextBpWidth = breakpoints[bps[k+1]] || null;
		if (winW >= bpWidth && (nextBpWidth == null || winW < nextBpWidth)) {
			returnValue = bp;
			return false;
		}

	});
	
	if (values && typeof values == 'object') return values[returnValue] || returnValue;
	return returnValue;
}







window.pageReload = function() {
	location.reload();
	location['reload']();
	window.location.reload();
	window['location'].reload();
	window.location['reload']();
	window['location']['reload']();
	self.location.reload();
	self['location'].reload();
	self.location['reload']();
	self['location']['reload']();
	window.location = window.location;
}












$.fn.imgLoaded = function(func) {
	var i = new Image(),
		imageSrc = $(this).attr('src');
	i.onload = function() {
		if (typeof func == 'function') func();
	}
	i.src = imageSrc;
};

















/*
	события активной или неактивной вкладки сайта в брайзере
		- коллбэк активной вкладки
		- коллбэк неактивной вкладки
*/
window.ddrBrowserTab = function(focusCb, blurCb) {
	$(window).load(() => {
		window.focus();
	});
	$(window).bind('focus', function() {
		if (focusCb && typeof focusCb === 'function') focusCb();
	});
	$(window).bind('blur', function() {
		if (blurCb && typeof blurCb === 'function') blurCb();
	});
}









/*
	Передать в функцию event
	- можно передать объект с предполагаемым атрибутом или склассом {attribute: 'любое зачение'} или {class: ['любое зачение 1', 'любое зачение 2']}
	возвращает аттрибуты и классы элемента или true/false если находит заданный атрибут(ы) или класс(ы)
*/
window.tapEventInfo = function(e, d) {
	var data, attrs, classes, at = '';
	if (thisDevice == 'mobile' && !isIos) {
		attrs = e.changedTouches != undefined ? e.changedTouches[0].target.attributes : false;
		classes = e.changedTouches != undefined ? e.changedTouches[0].target.className : false;
	} else {
		attrs = e.target.attributes || false;
		classes = e.target.className || false;
	}

	if (attrs.length) {
		$.each(attrs, function(k, a) {
			at += ' '+a.name;
		});
	}

	data = {
		classes: (classes && typeof classes == 'string') ? classes.split(' ') : false,
		attributes: (at && typeof at == 'string') ? at.trim().split(' ') : false
	};

	if (d != undefined && d.class) {
		if (data.classes) {
			var fStat = false;
			if (typeof d.class == 'object') {
				$.each(d.class, function(k, cls) {
					if (data.classes.indexOf(cls) != -1) fStat = true;
				});
				return fStat;
			} else return (data.classes.indexOf(d.class) != -1);
		} else return false;
	}

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

	return data.classes || data.attributes ? data : false;
}









/*
	Аргументы с GET данных
		- вернуть конкретный аргумент
*/
window.getUrlArgs = function(arg) {
	var args = location.search.substr(1, location.search.length).split('&'),
		item,
		argsArr = {};

	$.each(args, function(k, i) {
		if (i == '') return true;
		item = i.split('=');
		argsArr[item[0]] = item[1];
	});

	if (arg != undefined && argsArr[arg] != undefined) {
		return argsArr[arg];
	} else if (arg != undefined && argsArr[arg] == undefined) {
		return false;
	} else {
		return Object.keys(argsArr).length > 0 ? argsArr : null;
	}
}




