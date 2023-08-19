const viewsPath = 'admin.section.system.render.orders';
	
export async function timesheetOrders(row = null, timesheetId = null) {
	if (!timesheetId) throw new Error('Ошибка! timesheetOrders -> не передан timesheetId');
	
	//const row = $(btn).closest('[ddrtabletr]');
	
	if (!$(row).hasAttr('opened')) {
		$('#timesheetList').find('[ddrtabletr][opened]').removeAttrib('opened');
		$('#timesheetList').find('div[timesheetorders]').remove();
		$(row).setAttrib('opened');
		await buildOrdersTable(row, timesheetId);
		
	} else {
		$(row).removeAttrib('opened');
		$(row).siblings('div[timesheetorders]').remove();
	}
	
}






export async function buildOrdersTable(row = null, timesheetId = null, cb = null) {
	if (_.isNull(row) || _.isNull(timesheetId)) {
		console.error('buildOrdersTable ошибка -> переданы не все аргументы!');
		return;
	}
	
	
	_buildOrdersTable();
	
	
	
	$.editTimesheetOrder = async (btn, orderId = null, orderNumber = null, timesheetId = null) => {
		if (_.isNull(orderId)) return false;
		
		const {
			popper,
			wait,
			setHtml,
			close,
			enableButtons,
		} = await ddrPopup({
			url: 'crud/orders/form',
			method: 'get',
			params: {order_id: orderId, views: 'admin.section.system.render.orders', action: 'edit'},
			title: `Редактировать заказ <span class="color-gray">${orderNumber}</span>`, // заголовок
			width: 600, // ширина окна
			disabledButtons: true,
			buttons: ['ui.close', {action: 'timesheetUpdateOrder', title: 'Обновить'}],
		});
		
		enableButtons('close');
		
		$('#orderFormPrice').number(true, 2, '.', ' ');
		
		$(popper).ddrInputs('change:one', () => {
			enableButtons(true);
		});
		
		
		$.timesheetUpdateOrder = async () => {
			wait();
			const formData = $(popper).ddrForm({order_id: orderId, timesheet_id: timesheetId});
			
			const {data, error, status, headers} = await ddrQuery.put('crud/orders/form', formData);
			
			wait(false);
			
			if (error) {
				$.notify('Ошибка обновления заказа!', 'error');
				console.log(error);
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(popper).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
				return;
			}
			
			if (data) {
				$.notify('Заказ успешно обновлен!')
				
				const {order, price, server_name, raw_data, link} = data;
				const orderRow = $(btn).closest('[ddrtabletr]');
				
				if (order) $(orderRow).find('[orderordernumber]').text(order);
				if (price) $(orderRow).find('[orderprice]').text($.number(price, 2, '.', ' '));
				if (server_name) $(orderRow).find('[orderservername]').text(server_name);
				if (raw_data) $(orderRow).find('[orderrawdata]').text(raw_data);
				if (link) $(orderRow).find('[orderlink]').setAttrib('onclick', `$.openLink(this, '${link}')`);
				
				close();
			}
		}
	}
	
	
	
	
	
	$.relocateTimesheetOrder = async (btn, orderId = null, timesheetId = null, orderNumber = null, type = 'move') => {
		if (_.isNull(orderId) || _.isNull(timesheetId)) return false;
		
		let action;
		switch(type) {
			case 'move':  // if (x === 'value1')
				action = 'Перенести заказ';
				break;

			case 'clone':  // if (x === 'value2')
				action = 'Допран заказа';
				break;

			default:
				action = '...';
				break;
		}	
		const views = 'admin.section.system.render.orders.relocate';
		
		let calendarObj;
		let abortContr;
		const {
			popper,
			wait,
			setHtml,
			close,
			onClose,
			enableButtons,
		} = await ddrPopup({
			url: 'crud/orders/relocate',
			method: 'get',
			params: {timesheet_id: timesheetId, type, views},
			title: `${action} <span class="color-gray">${orderNumber}</span>`, // заголовок
			width: 700, // ширина окна
			disabledButtons: true,
			buttons: ['ui.close', {action: 'relocateOrderAction', title: action}],
		});
		
		enableButtons(true);
		
		onClose(() => {
			calendarObj?.remove();
			abortContr?.abort();
		});
		
		let choosedTimesheetId = null;
		
		let isLoading = false;
		calendarObj = calendar('relocateOrderCalendar', {
			initDate: 'now',
			async onSelect(instance, date) {
				if (!date) return;
				
				abortContr = new AbortController();
				
				if (isLoading) {
					abortContr?.abort();
					isLoading = false;
				} 
				
				const ddrtableWait = $(popper).find('[ddrtable]').blockTable('wait');
				
				isLoading = true;
				const {data, error, status, headers} = await ddrQuery.get('crud/orders/relocate/get_timesheets', {timesheet_id: timesheetId, date, type, views}, {abortContr});
				isLoading = false;
				
				if (error) {
					console.log(error);
					$.notify(error?.message, 'error');
					return;
				}
				
				ddrtableWait.destroy();
				
				$(popper).find('[ddrtable]').blockTable('setdData', data);
				
				choosedTimesheetId = null;
			}
		});
		
		
		/*$.relocateOrderChooseDate = async (instance, date) => {
			
		}*/
		
		
		$.relocateOrderClearDate = () => {
			$(popper).find('[ddrtable]').blockTable('clear');
		}
		
		
		$.relocateOrderChooseTs = (row, isActive, tsId = null) => {
			if (isActive) return false
			$(row).closest('[ddrtablebody]').find('[ddrtabletr].active').removeClass('active');
			$(row).addClass('active');
			choosedTimesheetId = tsId;
		}
		
		
		
		$.relocateOrderAction = async () => {
			wait();
			const formData = $(popper).ddrForm({order_id: orderId, timesheet_id: timesheetId, choosed_timesheet_id: choosedTimesheetId});
			
			const {data, error, status, headers} = await ddrQuery.post('crud/orders/relocate', formData);
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				wait(false);
				return;
			}
			
			if (data?.stat == 'moved') {
				$.notify(`Заказ «${orderNumber}» успешно перенесен!`);
				_buildOrdersTable();
			} else if (data?.stat == 'cloned') {
				$.notify(`Заказ «${orderNumber}» успешно склонирован с новым статусом «Доп. ран»!`);
				_buildOrdersTable();
			} else if (data?.stat == 'updated') {
				$.notify(`Заказ «${orderNumber}» уже существует в выбранном событии! Обновился только статус!`, 'gray');
			} else {
				$.notify(`Заказ «${orderNumber}» со статусом «Доп. ран» уже существует в выбранном событии!`, 'gray');
			}
			
			close();
		}
		
	}
		
		
		
	
	
	
	
	
	
	
	
	
	async function _buildOrdersTable() {
		if ($(row).siblings('[timesheetorders]').length == 0) $(row).after('<div class="timesheetorders minh7rem-5px" timesheetorders></div>');
			
		const ordersWait = $(row).siblings('[timesheetorders]').ddrWait({
			iconHeight: '4rem',
			bgColor: '#ecf3f5',
		});
		
		const {data, error, status, headers} = await ddrQuery.get('crud/orders/timesheet_list', {timesheet_id: timesheetId, views: viewsPath});
		
		ordersWait.destroy();
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		
		$(row).siblings('[timesheetorders]').html(data);
		
		$(row).siblings('[timesheetorders]').find('[ddrtable]').blockTable('buildTable');
		
		const rowsCount = Number($(data).find('[ddrtablebody] [ddrtabletr]').length);
		let count = $(row).find('[orderscount]').text(rowsCount);
		
		if (_.isFunction(cb)) cb();
	}
	
}









export async function orderCommentsChat(orderId = null, orderName = null, rowBtn = null, cb = null) {
	if (_.isNull(orderId)) {
		console.error('orderCommentsChat ошибка -> не передан orderId!');
		return;
	}
	
	const {
		state, // isClosed
		popper,
		wait,
		setTitle,
		setButtons,
		loadData,
		setHtml,
		setLHtml,
		dialog,
		close,
		query,
		onScroll,
		disableButtons,
		enableButtons,
		setWidth
	} = await ddrPopup({
		url: 'crud/orders/comments',
		method: 'get',
		params: {views: viewsPath, order_id: orderId},
		title: `Комментарии к заказу <span class="color-gray-60">«${orderName}»</span>`,
		width: 700, // ширина окна
	});
	
	callFunc(cb);
	
	
	let chatVisibleHeight = $('#chatMessageList').outerHeight(),
		chatScrollHeight = $('#chatMessageList')[0]?.scrollHeight,
		chatMessageBlock = $(popper).find('#chatMessageBlock');
	$('#chatMessageList').scrollTop(chatScrollHeight - chatVisibleHeight);
	
	$(chatMessageBlock).focus();
	
	
	$(chatMessageBlock).on('keydown', function(event) {
		const {isShiftKey} = metaKeys(event);
		
		if (event.keyCode == 13 && !isShiftKey) {
			event.preventDefault();
			const mess = getContenteditable(chatMessageBlock);
			if (mess) {
				chatSendMesage(orderId, mess);
			}
		}
	});
	
		
	let stat = 0;					
	$(chatMessageBlock).ddrInputs('change', (block, event) => {
		const {isShiftKey} = metaKeys(event);
		
		let mess = getContenteditable(chatMessageBlock);
		
		if (mess && stat == 0) {
			$('#chatSendMesageBtn').ddrInputs('enable');
			stat = 1;
		} else if (!mess && stat == 1) {
			$('#chatSendMesageBtn').ddrInputs('disable');
			stat = 0;
		}
	});
	
	
	
	$.chatSendMesage = (btn, orderId) => {
		let mess = getContenteditable(chatMessageBlock);
		chatSendMesage(orderId, mess);
	}

	async function chatSendMesage(orderId = null, message = null) {
	 	const {data, error, status, headers} = await ddrQuery.post('crud/orders/send_comment', {order_id: orderId, message, views: viewsPath});
	 	
	 	if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		
		if (data) {
			$(chatMessageBlock).empty();
			$('#chatSendMesageBtn').ddrInputs('disable');
			$('#chatMessageList').append(data);
			
			let chatVisibleHeight = $('#chatMessageList').outerHeight(),
				chatScrollHeight = $('#chatMessageList')[0]?.scrollHeight,
				scrollTop = chatScrollHeight - chatVisibleHeight;
				
			if (scrollTop > 0) {
				$('#chatMessageList').stop().animate({
					scrollTop: scrollTop,
				}, 200, 'swing', function() {});
			}
			
			addNewCommentToRow(rowBtn, message);
			stat = 0;
		}
	 	
	}
	
}









export async function showStatusesTooltip(btn = null, orderId = null, timesheetId = null, stat = null, cb = null) {
	const statusesTooltip = $(btn).ddrTooltip({
		cls: 'noselect',
		offset: [-1, 3],
		placement: 'left-start',
		tag: 'noscroll',
		minWidth: '150px',
		minHeight: '120px',
		wait: {
			iconHeight: '40px'
		},
		onShow: async function({reference, popper, show, hide, destroy, waitDetroy, setContent, setData, setProps}) {
			const {data, error, status, headers} = await ddrQuery.get('crud/orders/statuses', {order_id: orderId, status: stat, views: viewsPath});
 	
		 	if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			setData(data);
			waitDetroy();
		}
	});
	
	
	
	
	$.setOrderStatus = async (li, status, isActive) => {
		if (isActive) return false;
		
		$(li).closest('[orderstatusestooltip]').find('[ordertatus]').removeClass('statusitem-active');
		
		$(li).addClass('statusitem-active');
		
		const {data, error, headers} = await ddrQuery.post('crud/orders/set_status', {order_id: orderId, timesheet_id: timesheetId, status});
 	
	 	if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		if (data) {
			const {name, icon, color} = data;
			const statBlock = $(btn);
			
			if (name) {
				$(statBlock).find('[rowstatustext]').text(name);
			}
			
			if (color) {
				$(statBlock).find('[rowstatuscolor]').css('background-color', `${color}`);
			}
			
			if (icon) {
				$(statBlock).find('[rowstatusicon]').setAttrib('class', `fa-solid fa-fw fa-${icon}`);
				if (color) $(statBlock).find('[rowstatusicon]').css('color', `${color}`);
			}
		}
		
		statusesTooltip.destroy();
	}
	
}













function addNewCommentToRow(btn = null, message = null) {
	if (_.isNull(btn) || _.isNull(message)) return false;
	
	const commentSelector = $(btn).closest('[ordercommentblock]').find('[rowcomment]');
	
	if ($(commentSelector).children('p:not([date])').length == 0) $(commentSelector).append(`<p class="fz12px">${message}</p>`);
	else $(commentSelector).children('p:not([date])').replaceWith(`<p class="fz12px">${message}</p>`);
	
	
	if ($(commentSelector).children('p[date]').length == 1) {
		
		const {
			year,
			month,
			day,
			hours,
			minutes,
			seconds
		} = ddrDateBuilder();
		
		$(commentSelector).children('p[date]').html(day.zero+'.'+month.zero+'.'+year.full+' '+hours.zero+':'+minutes.zero+' от меня');
	}
	
}