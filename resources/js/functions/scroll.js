
/*
	Запретить скролл
*/
window.disableScroll = function() {
	//var scrollPosition = [
	//  self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
	//  self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
	//];

	//$('html').setAttrib('scroll-position', scrollPosition.join('|'));
	ddrCssVar('previous-overflow', $('html').css('overflow'));
	//$('html').setAttrib('previous-overflow', $('html').css('overflow'));
	$('html').css('overflow', 'hidden');
	//window.scrollTo(scrollPosition[0], scrollPosition[1]);
}




/*
	Разрешить скролл
*/
window.enableScroll = function() {
	/*var scrollPosition = $('html').attr('scroll-position');
	if (scrollPosition) {
		scrollPosition = scrollPosition.split('|');
		$('html').css('overflow', $('html').attr('previous-overflow'));
		$('html').removeAttrib('scroll-position');
		$('html').removeAttrib('previous-overflow');
		$('html').removeAttrib('style');
		window.scrollTo(scrollPosition[0], scrollPosition[1]);
	}*/
	
	$('html').css('overflow', ddrCssVar('previous-overflow'));
	//$('html').removeAttrib('scroll-position');
	//$('html').removeAttrib('previous-overflow');
	//$('html').removeAttrib('style');
}












/*
	Зафиксировать элемент при скролле
		- настройки
			- класс, который назначается фиксируемоему элементу
			- положение относительно начала документа, при котором назначается скролл
*/
$.fn.scrollFix = function(settings) {
	var selector = this,
		scrTOut,
		scrollTop,
		ops = $.extend({
			cls: 'fixed',
			pos: 200,
		}, settings);

	scrollTop = $(window).scrollTop();
	if (scrollTop > ops.pos && !$(selector).hasClass(ops.cls)) {
		$(selector).addClass(ops.cls);
	} else if (scrollTop <= ops.pos && $(selector).hasClass(ops.cls)) {
		$(selector).removeClass(ops.cls);
	}

	$(window).scroll(function(e) {
		clearTimeout(scrTOut);
		scrTOut = setTimeout(function() {
			scrollTop = $(window).scrollTop();
			if (scrollTop > ops.pos && !$(selector).hasClass(ops.cls)) {
				$(selector).addClass(ops.cls);
			} else if (scrollTop <= ops.pos && $(selector).hasClass(ops.cls)) {
				$(selector).removeClass(ops.cls);
			}
		}, 10);
	});
};





$.fn.ddrScroll = function(callback, condition = true) {
	if (!callback || !_.isFunction(callback)) return;
	
	const selector = this;
	const randEventHash = generateCode('lLnlLllnnnLll');
	
	let lastScrollTop = 0;
	let accumulateDir;
	let accumulate = 0;
	
	$(selector).on('scroll.'+randEventHash, function(event) {
		if (!condition || !condition?.value) return;
		var st = $(event.target).scrollTop();
		if (st > lastScrollTop) {
			
			if (accumulateDir != 'down') {
				accumulate = 0;
				accumulateDir = 'down';
			}
			 
			accumulate += (st - lastScrollTop);
			
			callback({dir: 'down', top: st, step: st - lastScrollTop, accumulate});
		} else {
			
			if (accumulateDir != 'up') {
				accumulate = 0;
				accumulateDir = 'up';
			} 
			
			accumulate += Math.abs(st - lastScrollTop);
			
			callback({dir: 'up', top: st, step: lastScrollTop - st, accumulate});
		}
		lastScrollTop = st;
	});
	
	return {
		destroy() {
			$(selector).off('scroll.'+randEventHash);
		}
	}
}






/*
	top(scrPos)
	bottom(scrPos)
	both(scrPos)
*/
window.scroll = function({top = null, bottom = null, both = null}) {
	$(window).scroll(() => {
		scrTop = $(window).scrollTop();
		if (scrPos < scrTop) { // прокрутка вниз
			if (bottom && typeof bottom == 'function') bottom(scrTop);
		} else { // прокрутка вверх
			if (top && typeof top == 'function') top(scrTop);
		}
		if (both && typeof both == 'function') both(scrTop);
		scrPos = scrTop;
	});
}




