
window.blockTable = function(method = null, ...params) {
	const blockTableCls = new BlockTable;
	
	return blockTableCls[method](...params);
}


$.fn.blockTable = function(method = null, ...params) {
	const blockTableCls = new BlockTable;
	
	return blockTableCls[method](this, ...params);
}




class BlockTable {
	
	setdData(selector = false, data = null, coutItems = 1) {
		if (!selector || _.isNull(data)) return false;
		$(selector).find('[ddrtablebody]').html(data);
		$(selector).find('[ddrtablebody]').scrollTop($(selector).find('[ddrtablebody] [ddrtabletr]:first').outerHeight() * coutItems);
		this.buildTable(selector);
	}
	
	
	prependData(selector = false, data = false, coutItems = 1) {
		if (!selector || !data) return false;
		$(selector).find('[ddrtabletr]:first').before(data);
		$(selector).scrollTop($(selector).find('[ddrtabletr]:first').outerHeight() * coutItems);
		this.buildTable(selector);
	}
	
	appendData(selector = false, data = false) {
		if (!selector || !data) return false;
		$(selector).find('[ddrtabletr]:last').after(data);
		this.buildTable(selector);
	}
	
	
	
	removeRows(selector = false, enableRemoveCount = false, start = false, count = false) {
		if (!selector || enableRemoveCount === false || start === false || !count) return false;
		
		if ($(selector).find('[ddrtabletr]').length >= enableRemoveCount) {
			$(selector).find('[ddrtabletr]').slice(start, count).remove();
		}
	}
	
	
	removeRowsBefore(selector = false, enableRemoveCount = false, count = false) {
		if (!selector || enableRemoveCount === false || !count) return false;
		
		if ($(selector).find('[ddrtabletr]').length >= enableRemoveCount) {
			$(selector).find('[ddrtabletr]').slice(0, count).remove();
		}
	}
	
	removeRowsAfter(selector = false, enableRemoveCount = false, count = false) {
		if (!selector || enableRemoveCount === false || !count) return false;
		
		if ($(selector).find('[ddrtabletr]').length >= enableRemoveCount) {
			$(selector).find('[ddrtabletr]').slice(-count, $(selector).find('[ddrtabletr]').length).remove();
		}
	}
	
	
	/*
		Синхронизация скролла нескольких таблиц
			- селектор (должен быть только атрибут)
	*/
	scrollSync(syncSelector) {
		const selector = $('['+syncSelector+']');
		let hasScrollCls = false,
			scrTop = 0;
		
		if ($(selector).length == 0) {
			throw new Error('scrollSync нет селекторов!');
			return;
		}
		
		$(selector).on('mouseover touchenter touchstart', function(e) {
			hasScrollCls = true;
			$(this).addClass('ddrtablebody-scrollsync');
			$(selector).not(this).removeClass('ddrtablebody-scrollsync');
		});
		
		$(selector).scroll(function() {
			if (!hasScrollCls && $(selector).filter('.ddrtablebody-scrollsync').length == 0) {
				$(this).addClass('ddrtablebody-scrollsync');
			}
			
			if ($(this).hasClass('ddrtablebody-scrollsync') == false) return;
			scrTop = $(this).scrollTop();
			$(selector).not('.ddrtablebody-scrollsync').scrollTop(scrTop);
		});
		
		$(selector).on('scrollstop', {latency: 30}, function() {
			scrTop = $(this).scrollTop();
			$(selector).scrollTop(scrTop);
		});
	}
	
	
	
	
	
	
	/*
		Сформировать ширину столбцов таблицы
	*/
	buildTable(listSelector) {
		if (!listSelector.length) {
			console.info('blockTable -> buildTable не отработала, так как нет таблицы!');
			return;
		} 
		
		const selector = $(listSelector).closest('[ddrtable]'),
			headCells = $(selector).find('[ddrtablehead]').find('[ddrtabletr]').find('[ddrtabletdmain]').length
				? $(selector).find('[ddrtablehead]').find('[ddrtabletr]').find('[ddrtabletdmain]')
				: $(selector).find('[ddrtablehead]').find('[ddrtabletr]').find('[ddrtabletd]'),
			bodyRows = $(selector).find('[ddrtablebody] [ddrtabletr]');
		
		let cellsWidths = [];
		
		
		_initHeadCellsWidths();
		_buildBodyRows();
		
		
		let stat = 2;
		$(headCells).ddrWatch('resize', entries => {
			if (stat > 0) {
				cellsWidths = [];
				_initHeadCellsWidths();
				_buildBodyRows();
				stat--;
			}
		});
		
		
		
		
		function _initHeadCellsWidths() {
			$(headCells).each(function(index, cell) {
				let width = Math.max($(cell).width(), $(cell)[0].offsetWidth, $(cell)[0].clientWidth, $(cell).outerWidth());
				cellsWidths.push(width);
			});
		}
		
		
		function _buildBodyRows() {
			if (cellsWidths) {
				$(bodyRows).each(function(rIndex, row) {
					$.each(cellsWidths, function(cIndex, width) {
						$(row).find('[ddrtabletd]:eq('+cIndex+')').css('width', width+'px');
					});
					$(row).not('.ddrtable__tr_visible').addClass('ddrtable__tr_visible');
					if (bodyRows.length == rIndex + 1) $(row).setAttrib('ddrtablepartend');
				});
			} else {
				$(bodyRows).find('[ddrtabletd]').css('width', (100 / headCells.length)+'%');
			}
		}	
	}
	
	
	
	
	error(selector = false, errorText = 'Ошибка') {
		$(selector).find('[ddrtablebody]').setAttrib('error');
		$(selector).find('[ddrtablebody]').attr('error', errorText);
	}
	
	
	wait(selector = false, stat = true) {
		const rowHeight = $(selector).find('[ddrtablebody] [ddrtabletr]:first').outerHeight() || $(selector).find('[ddrtablebody]').innerHeight() - 4;
		
		const btwait = $(selector).find('[ddrtablebody]').ddrWait({
			iconHeight: (rowHeight - 6)+'px',
			bgColor: '#fffa',
		});
		
		if ($(selector).find('[ddrtablebody] [ddrtabletr]').length == 0)  $(selector).find('.ddrtable__body_scrolled').addClass('d-flex');
		
		const clearFn = this.clear;
		
		return {
			destroy() {
				clearFn(selector);
				$(selector).find('.ddrtable__body_scrolled').removeClass('d-flex');
				btwait.destroy();
			}
		};
	}
	
	clear(selector = false) {
		if (!selector) return false;
		$(selector).find('[ddrtablebody]').empty();
	}
}