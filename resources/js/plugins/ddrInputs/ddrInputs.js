export default class DdrInputs {
	
	inputs = [];
	
	constructor(items, method) {
		if (!items) return false;
		
		// Методы, которые могут применяться для блока - обертки, в котором находятся инпуты
		const blockMethods = ['change', 'state', 'enable', 'disable', 'hide', 'show', 'notouch', 'touch', 'addClass', 'removeClass'];
		
		if (blockMethods.includes(method)) {
			if (items.length == 1
				&& !(['input', 'select', 'textarea', 'button'].includes(getTagName(items[0])))
				&& !$(items[0]).hasAttr('contenteditable')
				&& !$(items[0]).hasAttr('datepicker')) {
					items = items.find('input, select, textarea, button, [contenteditable], [datepicker]');
				}
		}
		
		let allData = [];
		
		items.each(function(k, item) {
			let tag = item?.tagName?.toLowerCase(),
				type = typeof $(item).attr('contenteditable') !== 'undefined' ? 'contenteditable' : (item?.type ? item?.type?.toLowerCase()?.replace('select-one', 'select') : null),
				group = typeof $(item).attr('inpgroup') !== 'undefined' ? $(item).attr('inpgroup')+'-' : '',
				wrapperClass = findWrapByInputType.indexOf(type) !== -1 ? group+type : group+tag,
				wrapperSelector = $(item).closest('.'+wrapperClass).length ? $(item).closest('.'+wrapperClass) : false;
			
			allData.push({
				item,
				tag,
				type,
				group,
				wrapperClass,
				wrapperSelector
			});
		});
		
		this.inputs = allData || null;
	}
	
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------------------------------------
	
	
	
	
	value(val = null) {
		if (!this.inputs || val === null) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector && ['checkbox', 'radio', 'select'].indexOf(type) === -1) {
				if (val !== false) {
					if (type === 'contenteditable') $(item).text(val);
					else $(item).val(val);
					
					if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					if ($(wrapperSelector).hasClass(wrapperClass+'_noempty') === false && val) $(wrapperSelector).addClass(wrapperClass+'_noempty');
				} else {
					if (type === 'contenteditable') $(item).text('');
					else $(item).val('');
					
					if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					if ($(wrapperSelector).hasClass(wrapperClass+'_noempty')) $(wrapperSelector).removeClass(wrapperClass+'_noempty');
				}
			}
		});	
	}
	

	
	
	
	
	
	// optionsData - один или массив option {value = null, title = null, defaultSelected = false, selected = false, disabled = false}
	// insertElement - option до после или сместо которой вставить новую или новые option Если не указать - то убираетсяя все и заменятеся
	// insType – before after replace,
	setOptions(optionsData = null, insertElement = null, insType = 'after') {
		if (!this.inputs || optionsData === null) return false;
		
		if (Array.isArray(optionsData)) {
			let options = [];
			$.each(optionsData, function(key, {value = '', title = null, defaultSelected = false, selected = false, disabled = false}) {
				if (!value && !title) return false;
				if (value && !title) title = value;
				let opt = new Option(title, value, defaultSelected, selected);
				if (disabled) opt.disabled = true;
				options.push(opt);
			});
			
			
			this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
				if (wrapperSelector && ['select'].indexOf(type) !== -1) {
					
					if (!insertElement) {
						$(item).children('option').remove();
						$(item).html(options);
					} else if (insertElement == 'before') {
						$(item).prepend(options);
					} else if (insertElement == 'after') {
						$(item).children().append(options);
					} else if (insType == 'before') {
						$(item).children(insertElement).before(options);
					} else if (insType == 'after') {
						$(item).children(insertElement).after(options);
					} else if (insType == 'replace') {
						$(item).children(insertElement).replaceWith(options);
					}
					
					if (selected) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					}
					
					if (selected && !value) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_noempty')) $(wrapperSelector).removeClass(wrapperClass+'_noempty');
					} else if (selected && value) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_noempty') === false) $(wrapperSelector).addClass(wrapperClass+'_noempty');
					}
				}
			});	
			
		} else {
			
			let {value = '', title = null, defaultSelected = false, selected = false, disabled = false} = optionsData;
			
			if (!value && !title) return false;
			if (value && !title) title = value;
			let option = new Option(title, value, defaultSelected, selected);
			if (disabled) option.disabled = true;
			
			this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
				if (wrapperSelector && ['select'].indexOf(type) !== -1) {
					if (!insertElement) {
						$(item).children('option').remove();
						$(item).html(option);
					} else if (insertElement == 'before') {
						$(item).prepend(option);
					} else if (insertElement == 'after') {
						$(item).children().append(option);
					} else if (insType == 'before') {
						$(item).children(insertElement).before(option);
					} else if (insType == 'after') {
						$(item).children(insertElement).after(option);
					} else if (insType == 'replace') {
						$(item).children(insertElement).replaceWith(option);
					}
					
					if (selected) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					}
					
					if (selected && !value) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_noempty')) $(wrapperSelector).removeClass(wrapperClass+'_noempty');
					} else if (selected && value) {
						if ($(wrapperSelector).hasClass(wrapperClass+'_noempty') === false) $(wrapperSelector).addClass(wrapperClass+'_noempty');
					}
				}
			});	
		}
	}
	
		
	
	
	
	
	
	
	
	
	error(content = null) {
		if (!this.inputs) return false;
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (/* ['checkbox', 'radio'].indexOf(type) === -1 &&  */wrapperSelector) {
				if ($(wrapperSelector).hasClass(wrapperClass+'_error') === false) $(wrapperSelector).addClass(wrapperClass+'_error');
				//if (['checkbox', 'radio'].indexOf(type) !== -1) return false;
				if (content) {
					if (['checkbox', 'radio'].indexOf(type) === -1) {
						if ($(wrapperSelector).find('[errorlabel]').length == 0) $(wrapperSelector).append('<div errorlabel></div>');
						$(wrapperSelector).find('[errorlabel]').html('<div>'+content+'</div>');
					} else {
						if ($(wrapperSelector).find('[errorlabel]').length == 0) $(wrapperSelector).append('<div errorlabel></div>');
						$(wrapperSelector).find('[errorlabel]').html('<div>'+content+'</div>');
					}
				}
			}
		});	
	}
	
	
	
	clear(callback = null) {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if ($(wrapperSelector).hasClass(wrapperClass+'_error')) $(wrapperSelector).removeClass(wrapperClass+'_error');
		
			$(wrapperSelector).find('.'+wrapperClass+'__errorlabel').empty();
			
			$(wrapperSelector).addClass(wrapperClass+'_changed');
			$(wrapperSelector).removeClass(wrapperClass+'_noempty');
			
			
			if ($(item).hasAttr('date')) {
				$(item).setAttrib('date');
				$(wrapperSelector).find('[datepicker]').val('');
				$(wrapperSelector).find('.icon').html('<i class="fa-solid fa-fw fa-calendar-days"></i>'); 
			}
			
			
			if (['checkbox', 'radio'].indexOf(type) !== -1) {
				$(wrapperSelector).removeClass(wrapperClass+'_checked');
				if ($(item).is(':checked')) $(item).removeAttrib('checked');
			} else if (type === 'contenteditable') {
				$(item).empty();
			} else {
				$(item).val('');
			}
		});
		
		if (callback && typeof callback === 'function') callback(this.inputs);
	}
	
	
	
	
	state(comand = null, callback = false) {
		if (!this.inputs) return false;
		
		if (comand === 'clear') {
			this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
				if ($(wrapperSelector).hasClass(wrapperClass+'_error')) $(wrapperSelector).removeClass(wrapperClass+'_error');
				if ($(wrapperSelector).hasClass(wrapperClass+'_changed')) $(wrapperSelector).removeClass(wrapperClass+'_changed');
			
				$(wrapperSelector).find('.'+wrapperClass+'__errorlabel').empty();
			});
		}
		
		if (callback && typeof callback === 'function') callback(this.inputs);
	}
	
	
	
	
	change(callback = null, tOut = 0, mtd = 'on') {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			let changeTOut;
			if (type === 'contenteditable') {
				let keyDownVal;
				let waitKeyUp = false;
				$(item)[mtd]('keyup keydown', function(e) {
					let thisItem = this;
					if (e.type == 'keydown') {
						if (!waitKeyUp) keyDownVal = $(thisItem).html();
						waitKeyUp = true;
					} else if (e.type == 'keyup') {
						waitKeyUp = false;
						let thisKeyUpVal = $(thisItem).html();
						if (keyDownVal !== thisKeyUpVal) {
							keyDownVal = thisKeyUpVal;
							if (tOut) {
								clearTimeout(changeTOut);
								changeTOut = setTimeout(() => {
									if (callback && typeof callback === 'function') callback(this, e);
								}, tOut);
							} else {
								if (callback && typeof callback === 'function') callback(this, e);
							}
						}
					}
				});
			} else {
				clearTimeout(changeTOut);
				changeTOut = setTimeout(() => {
					$(item)[mtd]('input datepicker', function(event) {
						if (tOut) {
							clearTimeout(changeTOut);
							changeTOut = setTimeout(() => {
								if (callback && typeof callback === 'function') callback(this, event);
							}, tOut);
						} else {
							if (callback && typeof callback === 'function') callback(this, event);
						}
					});
				}, tOut);
			}	
		});	
	}
	
	
	
	
	change_one(callback = null, tOut = 0) {
		this.change(callback, tOut, 'one');
	}
	
	
	
	addClass(cls = null) {
		if (!this.inputs) return false;
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (!$(wrapperSelector).hasClass(cls)) $(wrapperSelector).addClass(cls);
		});
	}
	
	
	
	
	removeClass(cls = null) {
		if (!this.inputs) return false;
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if ($(wrapperSelector).hasClass(cls)) $(wrapperSelector).removeClass(cls);
		});
	}
	
	
	
	
	checked(stat = true) {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector && ['checkbox', 'radio'].indexOf(type) !== -1) {
				if (stat === true) {
					if ($(wrapperSelector).hasClass(wrapperClass+'_checked') === false) $(wrapperSelector).addClass(wrapperClass+'_checked');
					if (type == 'checkbox' && $(wrapperSelector).hasClass(wrapperClass+'_changed') === false)
						$(wrapperSelector).addClass(wrapperClass+'_changed');
					
					
					if (type == 'radio') {
						let radioName = $(item).attr('name');
						$('body').find('input[name="'+radioName+'"]').not(item).removeAttrib('checked');
						$('body').find('input[name="'+radioName+'"]').not(item).closest('.'+wrapperClass).removeClass(wrapperClass+'_checked');
					}
					
					if ($(item).prop('checked') === false) {
						$(item).prop('checked', true);
						$(item).setAttrib('checked');
						$(wrapperSelector).find('.'+wrapperClass+'__errorlabel').empty();
					}
					
				} else if (stat === false) {
					if ($(wrapperSelector).hasClass(wrapperClass+'_checked')) $(wrapperSelector).removeClass(wrapperClass+'_checked');
					if (type == 'checkbox' && $(wrapperSelector).hasClass(wrapperClass+'_changed') === false)
						$(wrapperSelector).addClass(wrapperClass+'_changed');
					
					if ($(item).prop('checked')) {
						$(item).prop('checked', false);
						$(item).removeAttrib('checked');
						$(wrapperSelector).find('.'+wrapperClass+'__errorlabel').empty();
					}
				}
			}
		});	
	}
	
	
	
	selected(val = null) {
		if (!this.inputs || val === null) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector && ['select'].indexOf(type) !== -1) {
				if (val !== false) {
					$(item).val(val);
					if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					if ($(wrapperSelector).hasClass(wrapperClass+'_noempty') === false) $(wrapperSelector).addClass(wrapperClass+'_noempty');
				} else {
					$(item).children('option').prop('selected', false);
					$(item).children('option:first').prop('selected', true);
					if ($(wrapperSelector).hasClass(wrapperClass+'_changed') === false) $(wrapperSelector).addClass(wrapperClass+'_changed');
					if ($(wrapperSelector).hasClass(wrapperClass+'_noempty')) $(wrapperSelector).removeClass(wrapperClass+'_noempty');
				}
			}
		});	
	}
	
	
	
	
	
	disable() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasClass(wrapperClass+'_disabled') === false) $(wrapperSelector).addClass(wrapperClass+'_disabled');
				if (type === 'contenteditable') {
					$(item).attr('contenteditable', false);
				} else {
					$(item).setAttrib('disabled');
					
					const iconSelector = $(item).parent().find('input[datepicker]').length ? $(item).parent().find('.icon') : false;
					if (iconSelector) {
						$(iconSelector).removeClass('icon_active');
						$(iconSelector).html('<i class="fa-solid fa-fw fa-calendar-days"></i>'); 
					}
				}
			}
		});	
	}
	
	
	
	
	enable() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasClass(wrapperClass+'_disabled')) $(wrapperSelector).removeClass(wrapperClass+'_disabled');
				if (type === 'contenteditable') {
					$(item).attr('contenteditable', true);
				} else {
					$(item).removeAttrib('disabled');
				}
			}
		});	
	}
	
	
	
	
	
	hide() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasAttrib('hidden') === false) $(wrapperSelector).setAttrib('hidden');
			}
		});	
	}
	
	
	
	show() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasAttrib('hidden')) $(wrapperSelector).removeAttrib('hidden');
			}
		});	
	}
	
	
	
	
	
	
	
	notouch() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasAttrib('notouch') === false) $(wrapperSelector).setAttrib('notouch');
			}
		});	
	}
	
	
	
	touch() {
		if (!this.inputs) return false;
		
		this.inputs.forEach(({item, tag, type, group, wrapperClass, wrapperSelector}) => {
			if (wrapperSelector) {
				if ($(wrapperSelector).hasAttrib('notouch')) $(wrapperSelector).removeAttrib('notouch');
			}
		});	
	}
	
	
	
	
	
	
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------------
	
	
	
	
	
	
	
	
	
	
	//async #compress(file, {compress, watermark, background}) {
	//	const {default: Compressor} = await import('compressorjs');
	//	
	//	return new Promise((resolve, reject) => {
	//		new Compressor(file, Object.assign(compress, {
	//			beforeDraw(context, canvas) {
	//				if (background) {
	//					context.fillStyle = background;
	//					context.fillRect(0, 0, canvas.width, canvas.height);
	//				}
	//				//context.filter = 'grayscale(100%)';
	//			},
	//			/*drew(context, canvas) {
	//				if (watermark) {
	//					context.fillStyle = watermark.color;
	//					context.font = watermark.font || '2rem serif';
	//					context.fillText(watermark.text, 20, canvas.height - 20);
	//				}	
	//			},*/
	//			success(file) {
	//				resolve(file);
	//			},
	//			error(err) {
	//				reject(err);
	//			},
	//		}));
	//	});	
	//}
	
	
	
	
	
}