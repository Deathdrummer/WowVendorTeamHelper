let pagWait, container;


;$.fn.ddrPagination = function(options = {}) {
	
	container = this;
	
	let {
		countPages,
		currentPage,
		itemsAround,
		storageId,
		activeClass,
		hiddenClass,
		onChangePage,
	} = _.assign({
		countPages: null,
		currentPage: 1,
		itemsAround: {xs: 2, sm: 2, md: 2, lg: 2, xl: 2, xxl: 2}, // Объект {брейкпоинт:количество}. Количество видимых элементов пагинации вокруг активного элемента. Если, например, 2 - то всего будет открыто 5 элементов.
		storageId: 'ddrPagination'+location.pathname, // Идентификатор для localstorege чтобы запоминать последнюю активную страницу
		activeClass: 'paginator__item_active',
		hiddenClass: 'paginator__item_hidden',
		onChangePage: false, // ID станицы и done()
	}, options);
	
	
	$(container).addClass('paginator');
	
	$(container).html(_buildHtml((itemsAround * 2 + 1), countPages));
	
	if (countPages > 1) _buildPages(countPages, currentPage);
	
	let startIndex = 0,
		ia = _.isObject(itemsAround) ? getCurrentBreakPoint(itemsAround) : Number(itemsAround),
		gt = (startIndex + (ia-1) + ((ia+1) - startIndex > 0 ? ((ia+1) - startIndex) : 0)),
		lt = (startIndex - ((ia*2+1) + (startIndex - countPages > -ia ? startIndex - countPages : -ia)));
	
	if (startIndex < (countPages - ia)) $(container).find('[pagination]:gt('+gt+')').addClass(hiddenClass);
	if (startIndex > (ia+1)) $(container).find('[pagination]:lt('+lt+')').addClass(hiddenClass);
	
	
	
	
	//------------------------------------------------------------------- Клик на цифру страницы
	$(container).on(tapEvent, '[pagination]:not(.'+activeClass+'):not([disabled])', function() {
		$(container).setAttrib('disabled');
		
		let thisItem = this,
			index = $(thisItem).index() - 1,
			activeItem = $('[pagination].'+activeClass),
			page = Number($(thisItem).attr('pagination')),
			gt = (index + (ia-1) + ((ia+1) - index > 0 ? ((ia+1) - index) : 0)),
			lt = (index - ((ia*2+1) + (index - countPages > -ia ? index - countPages : -ia)));
		
		
		localStorage.setItem(storageId, $(thisItem).index() - 2);

		$('[pagination].'+activeClass).removeClass(activeClass);
		$(thisItem).addClass(activeClass);
		
		$(container).find('[pagination].'+hiddenClass).removeClass(hiddenClass);
		
		if ((itemsAround * 2 + 1) < countPages) {
			if (index < (countPages - ia)) $(container).find('[pagination]:gt('+gt+')').addClass(hiddenClass);
			if (index > (ia+1)) $(container).find('[pagination]:lt('+lt+')').addClass(hiddenClass);
		}
			
		
		
		if (page == 0) {
			$(container).find('[paginationrule="prev"]').setAttrib('disabled');
			$(container).find('[paginationrule="start"]').setAttrib('disabled');
		} else {
			$(container).find('[paginationrule="prev"][disabled]').removeAttrib('disabled');
			$(container).find('[paginationrule="start"][disabled]').removeAttrib('disabled');
		} 
		
		if (!$(thisItem).next().attr('pagination')) {
			$(container).find('[paginationrule="next"]').setAttrib('disabled');
			$(container).find('[paginationrule="end"]').setAttrib('disabled');
		} else {
			$(container).find('[paginationrule="next"][disabled]').removeAttrib('disabled');
			$(container).find('[paginationrule="end"][disabled]').removeAttrib('disabled');
		}
		
		if (_.isFunction(onChangePage)) {
			onChangePage((page + 1), function() {
				$(container).removeAttrib('disabled');
			});
		} else {
			console.error('ddrPagination -> Ошибка! Не указана функция переключения страницы!');
		}
	});
	
	
	
	
	
	
	//------------------------------------------------------------------- Клик на стрелку
	$(container).on(tapEvent, '[paginationrule]:not([disabled])', function() {
		$(container).setAttrib('disabled');
		
		let dir = $(this).attr('paginationrule'),
			activeItem = $(container).find('[pagination].'+activeClass).attr('pagination'),
			nextIndex = (dir == 'prev' ? (parseInt(activeItem)-1) : (dir == 'next' ? (parseInt(activeItem)+1) : (dir == 'start' ? 0 : (dir == 'end' ? (countPages-1) : 0)))),
			nextItem = $(container).find('[pagination="'+nextIndex+'"]'),
			page = Number($(nextItem).attr('pagination')),
			index = $(nextItem).index() - 1,
			gt = (index + (ia-1) + ((ia+1) - index > 0 ? ((ia+1) - index) : 0)),
			lt = (index - ((ia*2+1) + (index - countPages > -ia ? index - countPages : -ia)));
		
		
		localStorage.setItem(storageId, nextIndex);
		
		$(container).find('[pagination].'+activeClass).removeClass(activeClass);
		$(nextItem).addClass(activeClass);
		
		$(container).find('[pagination].'+hiddenClass).removeClass(hiddenClass);

		
		if ((itemsAround * 2 + 1) < countPages) {
			if (index < (countPages - ia)) $(container).find('[pagination]:gt('+gt+')').addClass(hiddenClass);
			if (index > (ia+1)) $(container).find('[pagination]:lt('+lt+')').addClass(hiddenClass);
		}
		
		if ((dir == 'prev' || dir == 'start') && $(nextItem).attr('pagination') == 0) {
			$(container).find('[paginationrule="prev"]').setAttrib('disabled');
			$(container).find('[paginationrule="start"]').setAttrib('disabled');
		} else {
			$(container).find('[paginationrule="prev"][disabled]').removeAttrib('disabled');
			$(container).find('[paginationrule="start"][disabled]').removeAttrib('disabled');
		} 
		
		if ((dir == 'next' || dir == 'end') && !$(nextItem).next().attr('pagination')) {
			$(container).find('[paginationrule="next"]').setAttrib('disabled');
			$(container).find('[paginationrule="end"]').setAttrib('disabled');
		} else {
			$(container).find('[paginationrule="next"][disabled]').removeAttrib('disabled');
			$(container).find('[paginationrule="end"][disabled]').removeAttrib('disabled');
		}
		
		if (_.isFunction(onChangePage)) {
			onChangePage((page + 1), function() {
				$(container).removeAttrib('disabled');
			});
		} else {
			console.error('ddrPagination -> Ошибка! Не указана функция переключения страницы!');
		}
	});
	
	
	
	
	
	return {
		pagRefresh(ops = {}) {
			const {
				countPages: cp,
				currentPage,
			} = _.assign({
				countPages: null,
				currentPage: 1,
			}, ops);
			
			countPages = cp;
			
			$(container).html(_buildHtml((itemsAround * 2 + 1), countPages));
			_setWait();
			if (countPages > 1) _buildPages(countPages, currentPage);
			
			gt = (startIndex + (ia-1) + ((ia+1) - startIndex > 0 ? ((ia+1) - startIndex) : 0));
			lt = (startIndex - ((ia*2+1) + (startIndex - countPages > -ia ? startIndex - countPages : -ia)));
			
			if (startIndex < (countPages - ia)) $(container).find('[pagination]:gt('+gt+')').addClass(hiddenClass);
			if (startIndex > (ia+1)) $(container).find('[pagination]:lt('+lt+')').addClass(hiddenClass);
			
			_removeWait();
		},
		onPageChange(cb = null) {
			if (!_.isFunction(cb)) console.error('ddrPagination -> onPageChange Ошибка! Не указана функция коллбэк!');
			
			$(container).on(tapEvent, '[paginationrule]:not([disabled])', function() {
				let page = Number($(container).find('[pagination].'+activeClass).attr('pagination'));
				cb(page + 1);
			});
			
			$(container).on(tapEvent, '[pagination]:not(.'+activeClass+'):not([disabled])', function() {
				let page = Number($(this).attr('pagination'));
				cb(page + 1);
			});
		}
	};
	
	
};






function _buildHtml(minCountPages, countHasPages) {
	return '<ul class="paginator__list paginator__list_start">'+
		(minCountPages < countHasPages ? '<li class="paginator__item" disabled paginationrule="start"><i class="fa-solid fa-angles-left"></i></li>' : '')+
		(countHasPages > 1 ? '<li class="paginator__item" disabled paginationrule="prev"><i class="fa-solid fa-angle-left"></i></li>' : '')+
		'<div pgplacer></div>'+
		(countHasPages > 1 ? '<li class="paginator__item" paginationrule="next"><i class="fa-solid fa-angle-right"></i></li>' : '')+
		(minCountPages < countHasPages ? '<li class="paginator__item" paginationrule="end"><i class="fa-solid fa-angles-right"></i></li>' : '')+
	'</ul>';
}


function _buildPages(pagesCount = false, currentPage = 1) {
	if (!pagesCount) return;
	const pgPlacer = $(container).find('[pgplacer]');
	
	let pagesHtml = '';
	for(let i = 0; i < pagesCount; i++) {
		pagesHtml += `<li class="paginator__item${i == (currentPage - 1) ? ' paginator__item_active' : ''}" pagination="${i}">${i+1}</li>`;
	}
	
	if ($(pgPlacer).length) {
		$(pgPlacer).replaceWith(pagesHtml);
	} else {
		$(container).find('[pagination]').replaceWith(pagesHtml);
	}
}



function _removePages() {
	const pages = $(container).find('[pagination]');
	
	if ($(pages).length) {
		$(pages).remove().after('<div pgplacer></div>');
	}
}


function _setWait() {
	if (pagWait !== undefined) {
		pagWait.on();
	} else {
		pagWait = $(container).ddrWait({
			iconHeight: '20px',
			iconColor: 'hue-rotate(170deg)',
			bgColor: '#eff0f5ee',
		});
	}
}



function _removeWait() {
	pagWait.off();
}
