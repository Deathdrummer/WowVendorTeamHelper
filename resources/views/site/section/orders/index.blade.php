<section>
	
	<div class="row flex-column justify-conetent-between h100">
		<div class="col-auto">
			<div class="row gx-30">
				<div class="col-auto">
					<x-chooser
						variant="neutral"
						group="normal"
						disabled
						px="25"
						id="ordersTypesChuser"
						class="mb1rem"
						>
						<x-chooser.item
							id="chooserAll"
								action="getOrdersAction:new"
								active>
								Выходящие
						</x-chooser.item>
						<x-chooser.item
							id="chooserAll"
								action="getOrdersAction:wait">
								Лист ожидания
						</x-chooser.item>
						<x-chooser.item
							id="chooserAll"
								action="getOrdersAction:cancel">
								Отмененные
						</x-chooser.item>
					</x-chooser>
				</div>
				<div class="col">
					<x-input
						size="normal"
						id="searchOrdersField"
						class="w25rem"
						placeholder="Поиск по номеру заказа..."
						icon="search"
						iconcolor="color-gray"
						iconaction="searchAction"
						{{-- hidden --}}
						/>
				</div>
			</div>
		</div>
		
		<div class="col">
			<x-card
				id="contractsCard"
				class="minh5rem"
				scrolled="calc(100vh - 216px)"
				loading
				>
				
				<ul class="ddrlist" id="ordersList"></ul>
			</x-card>
		</div>
		
		<div class="col-auto">
			<div class="d-flex justify-content-end mt1rem noselect">
				<div id="pagSelector"></div>
			</div>
		</div>
	</div>
	
</section>










<script type="module">
	const {getOrders, pag, status, currentPage, perPage, lastPage, total} = await loadSectionScripts({section: 'orders', guard: 'site'});
	const {orderCommentsChat} = await loadSectionScripts({section: 'timesheet', guard: 'admin'});
	
	await getOrders({init: true});
	
	const {pagRefresh, onPageChange} = pag('#pagSelector');
	
	let hasNewOrdersNoFirstPage = false;
	let searchStr = null;
	
	$('#contractsCard').card('ready');
	$('#ordersTypesChuser').removeAttrib('disabled');
	
	
	
	
	
	$('#searchOrdersField').ddrInputs('change', async function(inp, event) {
		$(inp).ddrInputs('disable');
		$('#ordersTypesChuser').setAttrib('disabled');
		
		const str = event?.target?.value || null;
		const icon = $(inp).siblings('.postfix_icon').find('i');
		
		searchStr = str;
		
		if (str) {
			$(icon).removeClass('fa-search');
			$(icon).addClass('fa-close');
		} else {
			$(icon).removeClass('fa-close');
			$(icon).addClass('fa-search');
			$('#searchOrdersField').ddrInputs('state', 'clear');
		}
		
		
		currentPage.value = 1;
		
		await getOrders({search: str});
		
		pagRefresh({
			countPages: lastPage.value,
			currentPage: 1,
		});
		
		$('#ordersTypesChuser').removeAttrib('disabled');
		$(inp).ddrInputs('enable');
		$(inp).focus();
		
	}, 300);
	
	
	
	$.searchAction = async (icon) => {
		if (searchStr) {
			$('#searchOrdersField').ddrInputs('disable');
			$('#ordersTypesChuser').setAttrib('disabled');
			$(icon).find('i').removeClass('fa-close');
			$(icon).find('i').addClass('fa-search');
			
			$('#searchOrdersField').ddrInputs('value', false);
			searchStr = null;
			
			await getOrders();
		
			pagRefresh({
				countPages: lastPage.value,
				currentPage: 1,
			});
			
			$('#searchOrdersField').ddrInputs('enable');
			$('#searchOrdersField').ddrInputs('state', 'clear');
			$('#ordersTypesChuser').removeAttrib('disabled');
			
		} else {
			//$(icon).find('i').removeClass('fa-search color-gray');
			//$(icon).find('i').addClass('fa-close color-red');
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	$.getOrdersAction = async (selector, isActive, stat) => {
		if (isActive) return;
		$('#ordersTypesChuser').setAttrib('disabled');
		status.value = stat;
		currentPage.value = 1;
		
		await getOrders({search: searchStr});
		
		pagRefresh({
			countPages: lastPage.value,
			currentPage: 1,
		});
		
		$('#ordersTypesChuser').removeAttrib('disabled');
	}
	
	
	$.openLink = (btn, url) => {
		if (!url) return;
		window.open(url, '_blank');
	}
	
	
	
	onPageChange((page) => {
		if (hasNewOrdersNoFirstPage) {
			pagRefresh({
				countPages: lastPage.value,
				currentPage: page,
			});
			
			hasNewOrdersNoFirstPage = false;
		}
	});
	
	
	
	
	
	
	
	$.toWaitListBtn = async (btn, order_id = null) => {
		if (_.isNull(order_id)) return;
		
		const views = 'movelist_form';
		
		const {
			popper,
			wait,
			close,
		} = await ddrPopup({
			url: 'client/orders/to_wait_list',
			params: {views},
			method: 'get',
			title: 'В лист ожидания',
			width: 400, // ширина окна
			buttons: ['ui.cancel', {title: 'Перенести', variant: 'green', action: 'toWaitListAction'}],
			centerMode: true, // контент по центру
		});
		
		
		$.toWaitListAction = async (__) => {
			wait();
			
			const message = $(popper).find('#comment').val();
			
			const {data, error, status, headers} = await ddrQuery.post('client/orders/to_wait_list', {order_id, message});
			
			if (error) {
				$.notify(error?.message, 'error');
				wait(false);
				return false;
			}
			
			if (data) {
				const row = $(btn).closest('[order]');
				$(row).remove();
				$.notify('Заказ успешно перенесен!');
				close();
			}
		}
	}
	
	
	
	
	
	
	
	$.toCancelListBtn = async (btn, order_id = null) => {
		if (_.isNull(order_id)) return;
		
		const views = 'movelist_form';
		
		const {
			popper,
			wait,
			close,
		} = await ddrPopup({
			url: 'client/orders/to_cancel_list',
			params: {views},
			method: 'get',
			title: 'В отмененные',
			width: 400, // ширина окна
			buttons: ['ui.cancel', {title: 'Перенести', variant: 'green', action: 'toCancelListAction'}],
			centerMode: true, // контент по центру
		});
		
		
		$.toCancelListAction = async (__) => {
			wait();
			
			const message = $(popper).find('#comment').val();
			
			const {data, error, status, headers} = await ddrQuery.post('client/orders/to_cancel_list', {order_id, message});
			
			if (error) {
				$.notify(error?.message, 'error');
				wait(false);
				return false;
			}
			
			if (data) {
				const row = $(btn).closest('[order]');
				$(row).remove();
				$.notify('Заказ успешно перенесен!');
				close();
			}
		}
	}
	
	
	
	
	
	
	
	
	$.toTimesheetBtn = async (rowBtn, order_id = null, date = null, orderNumber = null) => {
		if (_.isNull(order_id)) return;
		
		const action = 'Перенести заказ';
		const views = 'site.section.orders.render.relocate';
		let calendarObj;
		let abortContr;
		let isLoading = false;
		
		const {
			popper,
			wait,
			setHtml,
			close,
			enableButtons,
			onClose,
		} = await ddrPopup({
			url: 'client/orders/relocate',
			method: 'get',
			params: {order_id, views},
			title: `Привязать заказ: <span class="color-gray">${orderNumber}</span> к событию`, // заголовок
			width: 700, // ширина окна
			disabledButtons: true,
			buttons: ['ui.close', {action: 'relocateOrderAction', title: 'Привязать'}],
		});
		
		onClose(() => {
			calendarObj?.remove();
		});
		
		await loadTimesheets(date);
		
		enableButtons(true);
		
		calendarObj = calendar('relocateOrderCalendar', {
			initDate: date ? new Date(date) : 'now',
			async onSelect(instance, date) {
				if (!date) return;
				await loadTimesheets(date);
			}
		});
		
		async function loadTimesheets(date = null) {
			if (_.isNull(date)) return;
			const ddrtableWait = $(popper).find('[ddrtable]').blockTable('wait');
			
			if (isLoading) {
				abortContr?.abort();
				isLoading = false;
			} 
			
			abortContr = new AbortController();
			
			const d = new Date(date);
			let buildedDate = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0);
			
			isLoading = true;
			const {data, error, status, headers, abort} = await ddrQuery.get('client/orders/relocate/get_timesheets', {date: buildedDate, order_id, views}, {abortContr});
			isLoading = false;
			
			if (abort) return;
			
			ddrtableWait.destroy();
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			$(popper).find('[ddrtable]').blockTable('setdData', data);
			
			$(popper).find('[name="timesheet_id"]').val(headers['x-timesheet-id'] || null);
		}
		
		
		$.relocateOrderChooseTs = (row, isActive, tsId = null) => {
			if (isActive) return false
			$(row).closest('[ddrtablebody]').find('[ddrtabletr].active').removeClass('active');
			$(row).addClass('active');
			$(popper).find('[name="timesheet_id"]').val(tsId || null);
		}
		
		
		$.relocateOrderAction = async () => {
			wait();
			const formData = $(popper).ddrForm({order_id});
			
			const {data, error, status, headers} = await ddrQuery.post('client/orders/relocate', formData);
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				wait(false);
				return;
			}
			
			if (data) {
				const countOrders = $(rowBtn).closest('[order]').siblings('[order]').length;
				$(rowBtn).closest('[order]').remove();
				
				if (countOrders == 0) $('#ordersList').html('<p class="color-gray-300 text-center mt15px fz16px" noorders>Нет заказов</p>');
				
				$.notify(`Заказ «${orderNumber}» успешно перенесен в выбранное событие!`);
			} else {
				$.notify(`Не удалось перенести заказ «${orderNumber}» в выбранное событие!`, 'error');
			}
			
			close();
		}
	}
	
	
		
	
	
	$.openCommentsWin = (btn, orderId, orderName) => {
		orderCommentsChat(orderId, orderName, btn);
	}
	
		
	
	
	/*$.relocateTimesheetOrder = async (btn, orderId = null, timesheetId = null, orderNumber = null, type = 'move') => {
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
		const {
			popper,
			wait,
			setHtml,
			close,
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
		
		let choosedTimesheetId = null;
		
		$.relocateOrderChooseDate = async (instance, date) => {
			const ddrtableWait = $(popper).find('[ddrtable]').blockTable('wait');
			
			const {data, error, status, headers} = await ddrQuery.get('crud/orders/relocate/get_timesheets', {timesheet_id: timesheetId, date, type, views});
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			ddrtableWait.destroy();
			
			$(popper).find('[ddrtable]').blockTable('setdData', data);
			
			choosedTimesheetId = null;
		}
		
		
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
			
			if (data == 'moved') {
				$.notify(`Заказ «${orderNumber}» успешно перенесен!`);
				_buildOrdersTable();
			} else if (data == 'cloned') {
				$.notify(`Заказ «${orderNumber}» успешно склонирован с новым статусом «Доп. ран»!`);
				_buildOrdersTable();
			} else if (data == 'updated') {
				$.notify(`Заказ «${orderNumber}» уже существует в выбранном событии! Обновился только статус!`, 'gray');
			} else {
				$.notify(`Заказ «${orderNumber}» со статусом «Доп. ран» уже существует в выбранном событии!`, 'gray');
			}
			
			close();
		}
		
	}*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	ringtone('notify.mp3');
	//----------------------------------------------------------------------------------------- Прослушка
	if (Echo.connector.channels['send_message_channel'] === undefined) {
		listenSendMessageChannel();
	}
	
	function listenSendMessageChannel() {
		Echo.channel('send_message_channel').listen('.incoming_orders', async ({orders}) => {
			if (!orders.length || status.value != 'new') return;
			
			if (currentPage.value != 1) {
				hasNewOrdersNoFirstPage = true;
				
				total.value = total.value + orders.length;
				lastPage.value = Math.ceil(total.value / perPage.value);
				
				return;
			}
			 
			orders = orders.reverse(); // реверс строк, в зависимости от текущей сортировки
			
			const countRowsInList = $('#ordersList').children().length; // кол-во уже имеющихся строк
			
			const {data: ordersHtml, error: ordersError, headers: ordersHeaders} = await ddrQuery.post('client/orders/incoming_orders', {
				orders,
				status: status.value,
				count_rows_in_list: countRowsInList,
				current_page: currentPage.value,
			});
			
			
			if (ordersError) {
				if (ordersError.status == 419) $.notify(`Ошибка загрузки заказов! Устарела сессия! Пожалуйста, переагрузите страницу!`, 'error');
				else $.notify(`Ошибка загрузки заказов!`, 'error');
				throw new Error(ordersError.message);
			}
			
			if (!ordersHtml) return;
			
			const newOrdersRowsCount = Number(ordersHeaders['orders_count'] || 0);
			const ordersPerPage = Number(ordersHeaders['per_page'] || 0);
			const ordersLastPage = Number(ordersHeaders['last_page'] || 1);
			let isFirstPage = currentPage.value == 1;
			
			// Если первая страница
			if (isFirstPage) {
				if (countRowsInList + newOrdersRowsCount > ordersPerPage) {
					let removeOldRowsCount = (countRowsInList + newOrdersRowsCount) - ordersPerPage + 1;
					$('#ordersList').find(`[order]:gt(-${removeOldRowsCount})`).remove();
				}
				
				if ($('#ordersList').find('[noorders]').length) $('#ordersList').find('[noorders]').remove();
				
				$('#ordersList').prepend(ordersHtml);
				
				pagRefresh({
					countPages: ordersLastPage,
					currentPage: 1,
				});
				
				$.notify(`Поступили новые заказы! ${newOrdersRowsCount} шт.`);
				ringtone('notify.mp3');
			}
		});
	}
	
		
	
</script>