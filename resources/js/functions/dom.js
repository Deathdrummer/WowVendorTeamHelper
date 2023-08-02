/*
	Получить контент из блока [contenteditable]
		- селектор
*/
window.getContenteditable = function(selector = null) {
	if (!selector) {
		if (isDev) console.error('getContenteditable -> не передан селектор');
		return false;
	} 
	
	function extractTextWithWhitespace(elems) {
		const browserType = getBrowserType();
		var ret = "";
	    var elem;
	    let  lineBreakNodeName = "BR"; // Use <br> as a default
	    
	    if (browserType == 'Google Chrome') {
	        lineBreakNodeName = "BR";
	    } else if (browserType == 'Microsoft Internet Explorer') {
	        lineBreakNodeName = "P";
	    } else if (browserType == 'Mozilla Firefox') {
	        lineBreakNodeName = "BR";
	    } else if (browserType == 'Opera') {
	        lineBreakNodeName = "BR";
	    }
	    
	    
	    for (var i = 0; elems[i]; i++) {
	        elem = elems[i];

	        if (elem.nodeType === 3 || elem.nodeType === 4) {
	            ret += elem.nodeValue;
	        }

	        if (elem.nodeName === lineBreakNodeName){
	            ret += "\n";
	        }

	        if (elem.nodeType !== 8) {
	            ret += extractTextWithWhitespace(elem.childNodes, lineBreakNodeName);
	        }
	    }

	    return ret;
	}
	
	
	return extractTextWithWhitespace(selector);
}






/*
	- method: [mutate, resize]
	- opsOrCb
	- callback
*/
$.fn.ddrWatch = function(method = null, opsOrCb = null, callback = null) {
	if (_.isNull(method)) throw Error('Ошибка! watch не указан метод!');
	if (_.isNull(callback) && _.isFunction(opsOrCb)) {
		callback = opsOrCb;
		opsOrCb = {};
	}
	
	if (!callback || !_.isFunction(callback)) throw Error('Ошибка! watch не указан коллбэк!');
	
	const selector = this;
	
	if (method == 'mutate') {
		let observer = new MutationObserver(callback);
		
		const {
			childList,
			subtree,
			attributes,
			attributeFilter,
		} = _.assign(opsOrCb, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['class'],
		});
		
		observer.observe($(selector)[0], {
			childList,
			subtree,
			attributes,
			attributeFilter,
		});
		
	} else if (method == 'resize') {
		let observer = new ResizeObserver((entries) => {
			callback(entries);
		});
		
		observer.observe($(selector)[0]);
	}
	
};









/*
	Добавить аттрибуты к тегу
		- название атрибута
		- условия {[название значения]: условия} (если в названии переменная - обернуть в [])
*/
window.setTagAttribute = function(attrName = null, rules = null, joinSign = ' ') {
	if (_.isNull(attrName)) throw new Error('Ошибка! setTagAttribute -> не указан атрибут');
	if (!_.isObject(attrName) && _.isNull(rules)) return '';
	if (_.isObject(attrName)) joinSign = rules || ' ';
	
	if (_.isObject(attrName)) {
		let allAttrsValues = '';
		$.each(attrName, function(attrNameItem, rulesItem) {
			
			let attrValueItem = [];
			
			if (_.isPlainObject(rulesItem)) {
				$.each(rulesItem, function(val, rule) {
					if (Boolean(rule)) attrValueItem.push(val);
				});
				
				if (attrValueItem.length == 0) return '';
				allAttrsValues += ' '+attrNameItem+'="'+attrValueItem.join(joinSign)+'"';
			
			} else if (_.isArray(rulesItem)) {
				$.each(rulesItem, function(k, val) {
					if (_.isPlainObject(val)) {
						$.each(val, function(v, r) {
							if (Boolean(r)) attrValueItem.push(v);
						});
					} else {
						attrValueItem.push(val);
					}
				});
				
				if (attrValueItem.length == 0) return '';
				allAttrsValues += ' '+attrNameItem+'="'+attrValueItem.join(joinSign)+'"';
				
			} else {
				allAttrsValues += Boolean(rulesItem) ? ' '+attrNameItem : '';
			}	
		});
		
		return allAttrsValues;
	}
	
	
	
	if (_.isPlainObject(rules)) {
		let attrValues = [];
		$.each(rules, function(val, rule) {
			if (Boolean(rule)) attrValues.push(val);
		});
		
		if (attrValues.length == 0) return '';
		return ' '+attrName+'="'+attrValues.join(joinSign)+'"';
	
	} else if (_.isArray(rules)) {
		let attrValues = [];
		$.each(rules, function(k, val) {
			attrValues.push(val);
		});
		
		if (attrValues.length == 0) return '';
		return ' '+attrName+'="'+attrValues.join(joinSign)+'"';
	}
	
	return Boolean(rules) ? ' '+attrName : '';
}







window.getTagName = function(selector = null) {
	if (_.isNull(selector)) throw new Error('Ошибка! getTagName -> не указан селектор');
	return selector?.tagName?.toLowerCase();
}





$.fn.disableDrop = function(extClasses, callback) {
	var selector = this;

	function action(e, cb) {
		if (extClasses && typeof extClasses != 'function') {
			var stat = false;
			if (typeof extClasses == 'object') {
				stat = Object.values(e.target.classList).some((element) => extClasses.includes(element));
			} else {
				if ($(e.target).hasClass(extClasses) != false) {
					stat = true;
				}
			}

			if (!stat) {
				e.preventDefault();
				if (cb && callback && typeof callback == 'function') callback(e.target);
			}
		} else {
			e.preventDefault();
			if (cb && extClasses && typeof extClasses == 'function') extClasses(e.target);
		}
	}

	$(selector).off(tapEvent).on(tapEvent, function(e) {
		action(e, true);
	});

	$(selector).off('dragover').on('dragover', function(e) {
		action(e);
	});

	$(selector).off('dragleave').on('dragleave', function(e) {
		action(e);
	});

	$(selector).off('drop').on('drop', function(e) {
		action(e, true);
	});
};
