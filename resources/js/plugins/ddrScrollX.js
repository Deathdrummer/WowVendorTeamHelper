/*
	Горизонтальная прокрутка блока мышью и колесиком
		- шаг прокрутки (для колеса)
		- скорость прокрутки (для колеса)
		- разрешить прокрутку колесом
		- Игнорировать селекторы
		- добавить блок к синхронному скроллу
*/
$.fn.ddrScrollX = function(params) {
	let block = this,
	{
		scrollStep,
		scrollSpeed,
		enableMouseScroll,
		ignoreSelectors,
		addict,
		moveKey,
		ignoreMoveKeys,
		classWhenMoved,
		scrollEnd,
	} = _.assign({
		scrollStep: 50,
		scrollSpeed: 100,
		enableMouseScroll: false,
		ignoreSelectors: false,
		addict: false,
		moveKey: false, // alt shift ctrl
		ignoreMoveKeys: [],
		classWhenMoved: null,
		scrollEnd: false
	}, params);
	
	if (ignoreSelectors) ignoreSelectors = _.isArray(ignoreSelectors) ? ignoreSelectors.join(', ') : ignoreSelectors;
	if (ignoreMoveKeys) ignoreMoveKeys = pregSplit(ignoreMoveKeys);
	
	
	//console.log(ignoreMoveKeys);
	
	if (enableMouseScroll === true) {
		$(block).mousewheel(function(e) {
			let tag = e.target?.tagName?.toLowerCase();
			if (!ignoreSelectors || isHover(ignoreSelectors)) {
				e.preventDefault();
				$(this).stop(false, true).animate({scrollLeft: ($(this).scrollLeft() + scrollStep * -e.deltaY)}, scrollSpeed);
			}
		});
	}
	
	
	// Скролл в конец
	if (scrollEnd) {
		let trackWidth = $(block).children().outerWidth();
		$(block).scrollLeft(trackWidth);
	}
	
	
	let mdEvent;
	
	$(block).mousedown(function(e) {
		mdEvent = e;
		const {isShiftKey, isCtrlKey, isCommandKey, isAltKey, isOptionKey, noKeys, isActiveKey} = metaKeys(e);
		const {isLeftClick, isRightClick, isCenterClick} = mouseClick(e);
		
		
		if (!isLeftClick) return;
		if (moveKey && !isActiveKey(moveKey)) return;
		if (ignoreMoveKeys && isActiveKey(ignoreMoveKeys)) return;
		
		if (!ignoreSelectors || isHover(ignoreSelectors) == false) {
			let startX = this.scrollLeft + e.pageX;
			$(block).mousemove(function (e) {
				
				mobeMouseDiff({
					mdEvent,
					mmEvent: e,
					radius: 3,
				}, () => {
					if (classWhenMoved) $(block).addClass(classWhenMoved);
				});
				
				
				pauseEvent(e);
				$(block).css('cursor', 'e-resize');
				let pos = startX - e.pageX;
				this.scrollLeft = pos;
				if (addict) $(addict)[0].scrollLeft = pos;
				return false;
			});
		}
		
		
		$(document).one('mouseup', function (e) {
			if (classWhenMoved) $(block).removeClass(classWhenMoved);
			if (!ignoreSelectors || isHover(ignoreSelectors) == false) {
				$(block).css('cursor', 'default');
				$(block).off("mousemove");
			}
		});
		
	});
};








function mobeMouseDiff(ops = {}, cb = null) {
	const {
		mdEvent,
		mmEvent,
		radius,
	} = ops;
	
	const mdx = mdEvent.clientX,
		mdy = mdEvent.clientY,
		mmx = mmEvent.clientX,
		mmy = mmEvent.clientY;
	
	
	if ((mdx < mmx - radius || mdx > mmx + radius) || (mdy < mmy - radius || mdy > mmy + radius)) {
		callFunc(cb);
	}			
}



function pauseEvent(e){
    if(e.stopPropagation) e.stopPropagation();
    if(e.preventDefault) e.preventDefault();
    e.cancelBubble=true;
    e.returnValue=false;
    return false;
}