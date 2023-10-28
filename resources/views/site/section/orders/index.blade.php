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
							action="getOrdersAction:new"
							active
							>Входящие</x-chooser.item>
						<x-chooser.item
							action="getOrdersAction:wait"
							>Лист ожидания</x-chooser.item>
						<x-chooser.item
							action="getOrdersAction:cancel"
							>Отмененные</x-chooser.item>
						<x-chooser.item
							action="getOrdersAction:necro"
							>Некрота</x-chooser.item>
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
				$.notify('Заказ успешно перенесен в лист ожидания!');
				close();
			}
		}
	}
	
	
	
	
	
	$.toNecroListBtn = async (btn, order_id = null) => {
		if (_.isNull(order_id)) return;
		
		const views = 'movelist_form';
		
		const {
			popper,
			wait,
			close,
		} = await ddrPopup({
			url: 'client/orders/to_necro_list',
			params: {views},
			method: 'get',
			title: 'В некроту',
			width: 400, // ширина окна
			buttons: ['ui.cancel', {title: 'Перенести', variant: 'green', action: 'toNecroListAction'}],
			centerMode: true, // контент по центру
		});
		
		
		$.toNecroListAction = async (__) => {
			wait();
			
			const message = $(popper).find('#comment').val();
			
			const {data, error, status, headers} = await ddrQuery.post('client/orders/to_necro_list', {order_id, message});
			
			if (error) {
				$.notify(error?.message, 'error');
				wait(false);
				return false;
			}
			
			if (data) {
				const row = $(btn).closest('[order]');
				$(row).remove();
				$.notify('Заказ успешно перенесен в некроту!');
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
			disableButtons,
			onClose,
		} = await ddrPopup({
			url: 'client/orders/relocate',
			method: 'get',
			params: {order_id, views},
			title: `Привязать заказ: <span class="color-gray">${orderNumber}</span> к событию`, // заголовок
			width: 700, // ширина окна
			disabledButtons: true,
			buttons: ['ui.close', {action: 'relocateOrderAction', title: 'Привязать'}, {action: 'relocateOrderFollowAction', title: 'Привязать и перейти'}],
		});
		
		let regionId = $('#toTSRegionsChuser').find('[regionid][active]').attr('regionid');
		let period = $('#toTimesheetActualPast').find('[period][active]').attr('period');
		
		onClose(() => {
			calendarObj?.remove();
		});
		
		await loadTimesheets(date, regionId, period);
		
		enableButtons('close');
		
		const initDate = date ? new Date(date) : new Date(Date.now());	
		calendarObj = calendar('relocateOrderCalendar', {
			initDate,
			minDate: initDate,
			async onSelect(instance, choosedDate) {
				if (!choosedDate) return;
				date = choosedDate;
				await loadTimesheets(date, regionId, period);
			},
		});
		
		
		
		
		
		async function loadTimesheets(date = null, region_id = null, period = null) {
			const ddrtableWait = $(popper).find('[ddrtable]').blockTable('wait');
			
			if (isLoading) {
				abortContr?.abort();
				isLoading = false;
			} 
			
			abortContr = new AbortController();
			
			//const d = new Date(date);
			//let buildedDate = dateStartOfDay(date);
			if (_.isNull(date)) date = new Date(Date.now());
			date = dateToTimestamp(date, {correct: 'startOfDay'});
			
			isLoading = true;
			const {data, error, status, headers, abort} = await ddrQuery.get('client/orders/relocate/get_timesheets', {date, region_id, period, order_id, views}, {abortContr});
			isLoading = false;
			
			if (abort) return;
			
			ddrtableWait.destroy();
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				return;
			}
			
			$(popper).find('[ddrtable]').blockTable('setdData', data);
			
			if (headers['x-timesheet-id'] || null) {
				enableButtons(null, true);
			} else {
				disableButtons(null, true);
			}
			
			$(popper).find('[name="timesheet_id"]').val(headers['x-timesheet-id'] || null);
		}
		
		
		
		$.toTimesheetChooseRegion = async (btn, isActive, regId = null) => {
			if (isActive) return false;
			if (!regId) return false;
			regionId = regId;
			await loadTimesheets(date, regionId, period);
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
			
			await loadTimesheets(date, regionId, period);
		}
		
		
		
		$.relocateOrderChooseTs = (row, isActive, tsId = null) => {
			if (isActive || !tsId) return false;
			$(row).closest('[ddrtablebody]').find('[ddrtabletr].active').removeClass('active');
			$(row).addClass('active');
			enableButtons(null, true);
			$(popper).find('[name="timesheet_id"]').val(tsId || null);
		}
		
		
		$.relocateOrderAction = async () => {
			await relocateOrder();
		}
		
		$.relocateOrderFollowAction = async () => {
			await relocateOrder(true);
			$('[loadsection="timesheet"]').trigger(tapEvent);
		}
		
		
		
		async function relocateOrder(withFollow = null) {
			wait();
			const formData = $(popper).ddrForm({order_id, withFollow});
			
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
			
			
			if (data && withFollow) {
				ddrStore('timesheet-choosedPeriod', data?.period);
				ddrStore('eventsRegion', data?.region);
				ddrStore('listType', data?.listType);
				ddrStore('timesheet-filter', {[regionId.value]: {command: data?.filterEventTypeId}}, true);
				ddrStore('timesheet-filter', {[regionId.value]: {eventtype: data?.filterCommandId}}, true);
				ddrStore('open-ts', formData?.timesheet_id);
			}
				
			close();
		}
		
	}
	
	
		
	
	
	$.openCommentsWin = (btn, orderId, orderName) => {
		orderCommentsChat(orderId, orderName, btn);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
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
				ringtone('notify2.mp3');
			}
		});
	}
	
		
	
</script>