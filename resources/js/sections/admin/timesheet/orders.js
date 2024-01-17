const viewsPath = 'admin.section.system.render.orders';
	
export async function timesheetOrders(row = null, timesheetId = null, scroll = false) {
	if (!timesheetId) throw new Error('Ошибка! timesheetOrders -> не передан timesheetId');
	
	//const row = $(btn).closest('[ddrtabletr]');
	
	if (!$(row).hasAttr('opened')) {
		$('#timesheetList').find('[ddrtabletr][opened]').removeAttrib('opened');
		$('#timesheetList').find('div[timesheetorders]').remove();
		$(row).setAttrib('opened');
		await buildOrdersTable(row, timesheetId, null, scroll);
		
	} else {
		$(row).removeAttrib('opened');
		$(row).siblings('div[timesheetorders]').remove();
	}
	
}



		
			
		




export async function buildOrdersTable(row = null, timesheetId = null, cb = null, scroll = false) {
	if (_.isNull(row) || _.isNull(timesheetId)) {
		console.error('buildOrdersTable ошибка -> переданы не все аргументы!');
		return;
	}
	
	_buildOrdersTable(scroll);
	
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
			width: 700, // ширина окна
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
				
				const {order, price, server_name, raw_data, link, rawDataHistory} = data;
				
				const orderRow = $(btn).closest('[ddrtabletr]');
				
				if (order) $(orderRow).find('[orderordernumber]').text(order);
				if (price) $(orderRow).find('[orderprice]').text($.number(price, 2, '.', ' '));
				if (server_name) $(orderRow).find('[orderservername]').text(server_name);
				if (link) $(orderRow).find('[orderlink]').setAttrib('onclick', `$.openLink(this, '${link}')`);
				
				
				if (raw_data) {
					if (rawDataHistory == 1) {
						$(orderRow).find('[orderrawhistory]').removeAttrib('hidden');
					} else {
						$(orderRow).find('[orderrawdata]').text(raw_data);
					}
					$(orderRow).find('[orderrawcounter]').attr('title', 'Изменений: '+rawDataHistory);
				} 
				
				close();
			}
		}
	}
	
	
	
	
	
	
	
	
	
	$.relocateTimesheetOrder = async (btn, orderId = null, timesheetId = null, orderNumber = null, type = 'move') => {
		if (_.isNull(timesheetId)) return false;
		const ordersArr = _tsOrdersByArg(orderId, (type == 'move' ? 'doprun' : null));
		
		let action;
		switch(type) {
			case 'move':  // if (x === 'value1')
				action = 'Перенести';
				break;

			case 'clone':  // if (x === 'value2')
				action = 'Допран';
				break;

			default:
				action = '...';
				break;
		}	
		const views = 'admin.section.system.render.orders.relocate',
			popupTitle = count => `${action} ${count > 1 ? (type == 'move' ? 'заказы' : 'заказов') : 'заказ <span class="color-gray">'+orderNumber+'</span>'}`;
		
		let calendarObj;
		let abortContr;
		const {
			popper,
			wait,
			setHtml,
			close,
			onClose,
			enableButtons,
			disableButtons,
		} = await ddrPopup({
			url: 'crud/orders/relocate',
			method: 'get',
			params: {timesheet_id: timesheetId, order_id: ordersArr, type, views},
			title: popupTitle(ordersArr.length), // заголовок
			width: 700, // ширина окна
			disabledButtons: true,
			buttons: ['ui.close', {action: 'relocateOrderAction', title: action}],
		});
		
		enableButtons('close');
		
		let regionId = $('#toTSRegionsChuser').find('[regionid][active]').attr('regionid');
		let period = $('#toTimesheetActualPast').find('[period][active]').attr('period');
		
		onClose(() => {
			calendarObj?.remove();
			abortContr?.abort();
		});
		
		let choosedTimesheetId = null;
		
		let isLoading = false;
		
		let date = new Date(Date.now());
		
		getTsEvents(date, regionId, period);
		
		
		calendarObj = calendar('relocateOrderCalendar', {
			initDate: date,
			minDate: date,
			async onSelect(instance, d) {
				if (!d) return;
				
				abortContr = new AbortController();
				
				if (isLoading) {
					abortContr?.abort();
					isLoading = false;
				} 
				
				date = d;
				getTsEvents(date, regionId, period);
				
				choosedTimesheetId = null;
			}
		});
		
		
		async function getTsEvents(date, region_id, period) {
			const ddrtableWait = $(popper).find('[ddrtable]').blockTable('wait');
			
			disableButtons(false);
			
			date = dateToTimestamp(date, {correct: 'startOfDay'});
			
			
			isLoading = true;
			const {data, error, status, headers} = await ddrQuery.get('crud/orders/relocate/get_timesheets', {timesheet_id: timesheetId, date, region_id, period, type, views}, {abortContr});
			isLoading = false;
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			ddrtableWait.destroy();
			
			$(popper).find('[ddrtable]').blockTable('setdData', data);
			
			
		}
		
		/*$.relocateOrderChooseDate = async (instance, date) => {
			
		}*/
		
		
		
		$.toTimesheetChooseRegion = async (btn, isActive, regId = null) => {
			if (isActive) return false;
			if (!regId) return false;
			regionId = regId;
			getTsEvents(date, regionId, period);
		}
		
		
		$.toTimesheetChooseActualPast = async (btn, isActive, tp = null) => {
			if (isActive) return false;
			if (!tp) return false;
			period = tp;
			
			if (period == 'past') {
				date = new Date(Date.now());
				calendarObj.setMin(null);
				calendarObj.setDate(date);
				calendarObj.setMax(new Date(Date.now()));
				
			} else if (period == 'actual') {
				date = new Date(Date.now());
				calendarObj.setMax(null);
				calendarObj.setDate(date);
				calendarObj.setMin(new Date(Date.now()));
			}
			
			getTsEvents(date, regionId, period);
		}
		
		
		
		
		
		
		$.relocateOrderClearDate = () => {
			$(popper).find('[ddrtable]').blockTable('clear');
		}
		
		
		$.relocateOrderChooseTs = (row, isActive, tsId = null) => {
			if (isActive || !tsId) return false;
			enableButtons(true);
			$(row).closest('[ddrtablebody]').find('[ddrtabletr].active').removeClass('active');
			$(row).addClass('active');
			choosedTimesheetId = tsId;
		}
		
		
		
		$.relocateOrderAction = async () => {
			wait();
			
			const formData = $(popper).ddrForm({order_id: ordersArr, timesheet_id: timesheetId, choosed_timesheet_id: choosedTimesheetId});
			
			const {data, error, status, headers} = await ddrQuery.post('crud/orders/relocate', formData);
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				wait(false);
				return;
			}
			
			if (!_.isNull(orderNumber)) {
				const on = orderNumber.replace('&amp;', '&'),
					status = data?.stat[ordersArr[0]];
				
				if (status == 'moved') {
					$.notify(`Заказ «${on}» успешно перенесен!`);
					_buildOrdersTable();
				} else if (status == 'cloned') {
					$.notify(`Заказ «${on}» успешно склонирован с новым статусом «Доп. ран»!`);
					_buildOrdersTable();
				} else if (status == 'updated') {
					$.notify(`Заказ «${on}» уже существует в выбранном событии! Обновился только статус!`, 'gray');
				} else {
					$.notify(`Заказ «${on}» со статусом «Доп. ран» уже существует в выбранном событии!`, 'gray');
				}
				
			} else {
				const statuses = data?.stat;
				let movedCounter = 0,
					clonedCounter = 0,
					updatedCounter = 0;
				
				ordersArr.forEach((oId) => {
					if (statuses[oId] == 'moved') {
						movedCounter += 1;
					} else if (statuses[oId] == 'cloned') {
						clonedCounter += 1;
					} else if (statuses[oId] == 'updated') {
						updatedCounter += 1;
					}
				});
				
				if (movedCounter > 0) {
					$.notify(`Заказы (${movedCounter} шт.) успешно перенесены!`);
					_buildOrdersTable();
				}
				
				if (clonedCounter > 0) {
					$.notify(`Заказы (${clonedCounter} шт.) успешно склонированы с новым статусом «Доп. ран»!`);
					_buildOrdersTable();
				}
				
				if (updatedCounter > 0) {
					$.notify(`Заказы (${updatedCounter} шт.) уже существуют в выбранном событии! Обновился только статус!`, 'gray');
				}
				
				if (movedCounter == 0 && clonedCounter == 0 && updatedCounter == 0) {
					$.notify(`Заказы со статусом «Доп. ран» уже существуют в выбранном событии!`, 'gray');
				}
			}
			
			close();
		}
		
		
	}
		
	
	
	
	
		
	
	
	
	
	$.detachTimesheetOrder = async (btn, orderId = null, timesheetId = null, orderNumber = null) => {
		let ordersArr = _tsOrdersByArg(orderId, 'doprun');
		
		const popupTitle = orderNumber ? `Отвязать заказ ${orderNumber}` : 'Отвязать заказы',
			notifyTitle = count => orderNumber ? `Заказ ${orderNumber} успешно отвязан!` : `Заказы (${count} шт.) успешно отвязаны!`,
			{
			popper,
			wait,
			close,
		} = await ddrPopup({
			url: 'crud/orders/detach',
			method: 'get',
			params: {views: 'admin.section.system.render.orders'},
			title: popupTitle, // заголовок
			width: 400, // ширина окна
			//html: `<p class="color-green fz16px">Отвязать заказ ${orderNumber} и перенести в лист ожидания?</p>`, // контент
			buttons: ['ui.close', {action: 'detachTimesheetOrderAction', title: 'Перенести'}], // массив кнопок
			centerMode: true, // контент по центру
		});
		
		let listType = null;
		$.detachOrderChangeListType = (select) => {
			listType = $(select).val();
			if (listType == -1) $('#detachWaitGroupBlock').removeAttrib('hidden');
			else {
				$('#detachWaitGroupBlock').setAttrib('hidden');
				$('#waitGroupSelect').ddrInputs('selected', false);
			}
		}
		
		
		$.detachTimesheetOrderAction = async (__) => {
			wait();
			
			const status = $(popper).find('#listType').val(), // В какой список отправить заказ
				dTCObject = decrementTimesheetCount(btn, true);
			
			let successCount = 0,
				totalOrders = ordersArr.length,
				waitGroup = listType == -1 ? $('#waitGroupSelect').val() : null;
			
			for await (const [index, orderId] of Object.entries(ordersArr)) {
				const {data, error, headers} = await ddrQuery.post('crud/orders/detach', {order_id: orderId, timesheet_id: timesheetId, status, wait_group: waitGroup}),
					isEnd = Number(index) + 1 == totalOrders,
					row = $(btn).closest('[timesheetorders]').find(`[tsorder="${orderId}"]`),
					notRows = $(row).closest('[timesheetorders]').find('[tsorder]').length == ordersArr.length;
				
				if (error) {
					console.log(error);
					$.notify(error?.message, 'error');
					wait(false);
					return;
				}
				
				if (data) {
					successCount += 1;
					if (notRows) {
						$(row).closest('[ddrtable]').replaceWith('<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>');
					} else {
						$(row).remove();
					}
				}
				
				if (isEnd) {
					if (successCount == totalOrders) $.notify(notifyTitle(totalOrders));
					else {
						if (orderNumber) $.notify(`Заказ ${orderNumber} не был отвязан!`, 'error');
						else $.notify(`Не все заказы не были отвязаны! ${successCount} из ${ordersArr.length}!`, 'error');
					}
					
					dTCObject.run(successCount);
					close();	
				}
			} // for\of	
		}
		
	}
	
		
		
	
	
	
	
	
	
	
	
	
	async function _buildOrdersTable(scroll = false) {
		if ($(row).siblings('[timesheetorders]').length == 0) $(row).after('<div class="timesheetorders minh7rem-5px" timesheetorders></div>');
			
		const ordersWait = $(row).siblings('[timesheetorders]').ddrWait({
			iconHeight: '4rem',
			bgColor: '#ecf3f5',
		});
		
		
		const search = $('#searchOrdersField').val() || null;
		
		const {data, error, status, headers} = await ddrQuery.get('crud/orders/timesheet_list', {timesheet_id: timesheetId, views: viewsPath, search});
		
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
		
		if (scroll) {
			let rowHeight = $('#timesheetList').find(`[tsevent]`).first().outerHeight(),
				rowIndex = $('#timesheetList').find(`[tsevent][opened]`).index();
			if (rowIndex > 0) $('#timesheetList').scrollTop(rowIndex * rowHeight);
		}
		
		if (_.isFunction(cb)) cb();
	}
	
}









export async function orderCommentsChat(orderId = null, orderName = null, rowBtn = null, cb = null) {
	if (_.isNull(orderId)) {
		console.error('orderCommentsChat ошибка -> не передан orderId!');
		return;
	}
	
	const {
		popper,
		wait,
		close,
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











export async function rawDataHistory(orderId = null, orderName = null, rowBtn = null, cb = null) {
	if (_.isNull(orderId)) {
		console.error('rawDataHistory ошибка -> не передан orderId!');
		return;
	}
	
	
	const {
		popper,
		wait,
		close,
	} = await ddrPopup({
		url: 'crud/orders/rawdatahistory',
		method: 'get',
		params: {views: viewsPath, order_id: orderId},
		title: `История изменений данных заказа: <span class="color-gray-60">«${orderName}»</span>`,
		width: 1000, // ширина окна
	});
	
	callFunc(cb);
	
}









export async function showStatusesTooltip(btn = null, orderId = null, timesheetId = null, rowStat = null, cb = null) {
	let ref, ttip, isMultiple = btn.localName == 'button';
	$(btn).addClass('notouch');
	const statusesTooltip = $(btn).ddrTooltip({
		cls: 'noselect',
		offset: [-1, 3],
		placement: 'left-start',
		tag: 'noscroll',
		minWidth: '150px',
		minHeight: '32px',
		wait: {
			iconHeight: '40px'
		},
		onShow: async function({reference, popper, show, hide, destroy, waitDetroy, setContent, setData, setProps}) {
			let order_id = _.isPlainObject(orderId) ? null : orderId;
			
			const {data, error, status, headers} = await ddrQuery.get('crud/orders/statuses', {order_id, status: rowStat, views: viewsPath});
			
			$(btn).removeClass('notouch');
		 	
		 	if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			setData(data);
			ref = reference;
			ttip = popper;
			waitDetroy();
		}
	});
	
	
	
	
	$.setOrderStatus = async (li, status, isActive) => {
		if (isActive) return false;
		
		let ordersIds = _buildOrdersIds(orderId, status),
			countChoosedOrders = ordersIds.length;
		if (!countChoosedOrders) {
			statusesTooltip.destroy();
			$.notify('Нет подходящих для выполнения заказов', 'info');
			return;
		}
		
		
		$(li).closest('[orderstatusestooltip]').find('[ordertatus]').removeClass('statusitem-active');	
		$(li).addClass('statusitem-active');
		
		const ttWait = $(ttip).ddrWait({
			iconHeight: '25px',
			bgColor: '#ffffffa1'
		});
		
		statusesTooltip.destroy();
		
		let title;
		let message = null;
		let groupId = null;
		if (status == 'wait') title = 'В лист ожидания';
		else if (status == 'cancel')  title = 'В отмененные';
		
		const url = {
			wait: 'to_wait_list',
			cancel: 'to_cancel_list',
		};
		
		if (['wait', 'cancel'].includes(status)) {
			const {
				popper,
				wait,
				close,
			} = await ddrPopup({
				url: `client/orders/${url[status]}`,
				params: {views: 'movelist_form', multiple: _.isPlainObject(orderId) ? 1 : 0},
				method: 'get',
				title,
				width: 400, // ширина окна
				buttons: ['ui.cancel', {title: 'Перенести', variant: 'green', action: 'setStatusAction'}],
				centerMode: true, // контент по центру
			});
			
			ttWait.destroy();
			
			$.setStatusAction = async (__) => {
				wait();
				
				message = $(popper).find('#comment').val();
				groupId = $(popper).find('#groupId').val();
				
				if (_.isEmpty(ordersIds)) {
					$.notify('Нет подходящих для выполнения заказов', 'info');
					return;
				}
				
				ordersIds.forEach(function(ordrId, index) {
					setStatusFunc(ordrId, (stat) => {
						if (!stat) wait(false);
						else close();
					}, popper, (index + 1 == ordersIds.length));
				});
				
				decrementTimesheetCount(btn, ordersIds.length);
			};
			
		} else {
			if (_.isEmpty(ordersIds)) {
				$.notify('Нет подходящих для выполнения заказов', 'info');
				return;
			}
			
			ordersIds.forEach(function(ordrId) {
				setStatusFunc(ordrId);
			});
			decrementTimesheetCount(btn, ordersIds.length);
		}
			
		
		// обрабатывает один заказ
		async function setStatusFunc(orderId = null, cb = null, popper = null, end = true) {
			const {data, error, headers} = await ddrQuery.post('crud/orders/set_status', {
				order_id: orderId,
				timesheet_id: timesheetId,
				message,
				group_id: groupId,
				status,
				current_status: rowStat
			});
			
		 	if (error) {
		 		console.log(error);
				$.notify(error?.message, 'error');
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						if (field == 'group_id') $(popper).find('[id="groupId"]').ddrInputs('error', errors[0]);
					});
				}
				callFunc(cb, false);
				return;
			}
			
			if (data) {
				if (['wait', 'cancel'].includes(status)) {
					let hasRows = !!$(ref).closest('[ddrtabletr]').siblings('[ddrtabletr]').length;
					
					if (rowStat != 'doprun') {
						if (hasRows) $(ref).closest('[ddrtabletr]').remove();
						else $(ref).closest('[ddrtable]').replaceWith('<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>');
					}
					
					let listNames = {
						wait: 'лист ожидания',
						cancel: 'отмененные',
					};
					
					$.notify(`Заказ успешно${rowStat != 'doprun' ? ' отвязан и' : ''} перенесен в ${listNames[status]}`);
					
				} else if (status == 'ready') {
					if (isMultiple) {
						if (data?.isHash) {
							$('#timesheetContainer').find(`[orderstatusblock="${orderId}"]`).replaceWith('<i class="fa-regular fa-fw fa-circle-check color-green fz18px" title="Подтвержден"></i>');
							$.notify(`Заказ успешно подтвержден!`);
						} else {
							$('#timesheetContainer').find(`[orderstatusblock="${orderId}"]`).replaceWith('<i class="fa-regular fa-fw fa-clock color-gray fz18px" title="На подтверждении"></i>');
							$.notify(`Заказ отправлен на подтверждение!`);
						}
						
					} else {
						if (data?.isHash) {
							$(btn).replaceWith('<i class="fa-regular fa-fw fa-circle-check color-green fz18px" title="Подтвержден"></i>');
							$.notify(`Заказ успешно подтвержден!`);
						} else {
							$(btn).replaceWith('<i class="fa-regular fa-fw fa-clock color-gray fz18px" title="На подтверждении"></i>');
							$.notify(`Заказ отправлен на подтверждение!`);
						}
					}
						
				} else {
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
					
					changeOnclickAttr(statBlock, rowStat, status);
				}
				
				if (end) callFunc(cb, true);
			}
		}
	}
	
}














let chooseTsOrdersCB;
export function chooseTsOrders(cb = null) {
	chooseTsOrdersCB = cb;
	
	if (_.isFunction(cb)) {
		$('#timesheetContainer').on('change', '[choosetsdorder]', function(e) {
			const container = $(e.target).closest('[timesheetorders]'),
				{ids, status_ids} = _getChoosedTsOrders();
			callFunc(cb, {container, ids, status_ids, hasChoosed: !!ids.length});
		});
	} else if (cb === true) {
		return _getChoosedTsOrders()['status_ids'];
	}
	
	return {
		chooseAllTsOrders(e) {
			const container = $(e.target).closest('[timesheetorders]'),
				ordersRowsCount = $(container).find('[choosetsdorder]').length,
				choosedCount = $(container).find('[choosetsdorder]:checked').length;
			
			if (ordersRowsCount > choosedCount) {
				$(container).find('[choosetsdorder]:not(:disabled)').ddrInputs('checked', true);
			} else if (ordersRowsCount == choosedCount) {
				$(container).find('[choosetsdorder]:not(:disabled)').ddrInputs('checked', false);
			}
			
			const {ids, status_ids} = _getChoosedTsOrders();
			callFunc(cb, {container, ids, status_ids, hasChoosed: !!ids.length});
			
			return {
				ids,
			};
		}
	};
}



/*	
	1. все - массив
	2. ID => статус - массив объектов
*/
function _getChoosedTsOrders() {
	const ordersItems = $('#timesheetContainer').find('[choosetsdorder]:checked'),
		choosedTsOrders = [],
		choosedTsOrdersStatus = {};
	
	for (let chOrder of ordersItems) {
		const attrStr = $(chOrder).attr('choosetsdorder'),
			[orderId, status] = ddrSplit(attrStr, '|');
		choosedTsOrders.push(Number(orderId));
		
		if (!choosedTsOrdersStatus[status]) choosedTsOrdersStatus[status] = [];
		choosedTsOrdersStatus[status].push(Number(orderId));
	}
	
	return {ids: choosedTsOrders, status_ids: choosedTsOrdersStatus};
}















//----------------------------------------------------------------------------------------




function decrementTimesheetCount(btn = null, removeCount = 1) {
	if (_.isNull(btn)) console.error('decrementTimesheetCount ошибка -> не передан btn');
	
	const tsRow = $(btn).closest('[timesheetorders]').prev('[ddrtabletr]').find('[orderscount]');
	
	const obj = {
		run(rCount = 1) {
			let count = Number($(tsRow).text());
			$(tsRow).text(count - rCount);
		}
	};
	
	if (removeCount === true) return obj;
	
	obj.run(removeCount);
}



function changeOnclickAttr(selector, search, replace) {
	let onclickAttr = $(selector).attr('onclick');
	onclickAttr = onclickAttr.replace(search, replace);
	$(selector).attr('onclick', onclickAttr);
}







function addNewCommentToRow(btn = null, message = null) {
	if (_.isNull(btn) || _.isNull(message)) return false;
	
	const commentSelector = $(btn).closest('[ordercommentblock]').find('[rowcomment]');
	
	if ($(commentSelector).children('p:not([date])').length == 0) $(commentSelector).append(`<p class="fz12px lh900 format wodrbreak color-gray-500">${message}</p>`);
	else $(commentSelector).children('p:not([date])').replaceWith(`<p class="fz12px lh900 format wodrbreak color-gray-500">${message}</p>`);
	
	
	if ($(commentSelector).children('p[date]').length == 1) {
		
		const {
			year,
			month,
			day,
			hours,
			minutes,
			seconds
		} = ddrDateBuilder();
		
		$(commentSelector).children('p[date]').html(day.zero+'.'+month.zero+'.'+year.full+' '+hours.zero+':'+minutes.zero+' от <span class="color-green">меня</span>');
	}
	
}



function _buildOrdersIds(ordersData = null, setStatus = null) {
	if (_.isNull(ordersData)) return false;
	
	if (!_.isPlainObject(ordersData)) return [ordersData];
	
	const ordersIds = [];
	for (const [status, orders] of Object.entries(ordersData)) {
		if (setStatus === true) {
			ordersIds.push(...orders);
			continue;
		}
		if (!_.isNull(setStatus) && (status == 'ready' || status == setStatus || (status == 'doprun' && ['new'].includes(setStatus)))) continue;
		ordersIds.push(...orders);
	}
	
	return ordersIds;
}



function _tsOrdersByArg(orderId = null, filter = true) {
	if (!_.isNull(orderId)) return [orderId];
	let choosedTsOrders = chooseTsOrders(true);
	return _buildOrdersIds(choosedTsOrders, filter);
}