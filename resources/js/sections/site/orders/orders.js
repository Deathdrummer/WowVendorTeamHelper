const status = ref('new');
const currentPage = ref(1);
const lastPage = ref(null);
const perPage = ref(null);
const total = ref(null);



async function getOrders(ops = {}) {
	const {
		init,
		search,
	} = _.assign({
		init: false, // обновление списка
		search: null, // поиск по номеру заказа
	}, ops);
	
	let wait;
	
	if (!init) {
		wait = $('#contractsCard').ddrWait({
			iconHeight: '40px',
			iconColor: 'hue-rotate(170deg)',
		});
	}
	
	const {data, error, headers} = await ddrQuery.get('client/orders', {
		status: status.value,
		current_page: currentPage.value,
		search,
	});
	
	currentPage.value = Number(headers['current_page']);
	perPage.value = Number(headers['per_page']);
	lastPage.value = Number(headers['last_page']);
	total.value = Number(headers['total']);
	
	if (!init) wait.destroy();
	
	
	if (error) {
		$('#ordersList').html('<p class="color-gray-300 text-center mt15px fz16px">Ошибка</p>');
		$.notify(`Ошибка завгрузки заказов!`, 'error');
		throw new Error(error.message);
	}
	
	
	if (data) {
		$('#ordersList').html(data);
	} else {
		$('#ordersList').html('<p class="color-gray-300 text-center mt15px fz16px" noorders>Нет заказов</p>');
	}
	
}






async function editOrder(btn, orderId = null, orderNumber = null) {
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
		const formData = $(popper).ddrForm({order_id: orderId});
		
		const {data, error, status, headers} = await ddrQuery.put('crud/orders/update_order', formData);
		
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






function pag(selector = null) {
	return $(selector).ddrPagination({
		countPages: lastPage.value,
		//currentPage: 3,
		itemsAround: 3,
		async onChangePage(page, done) {
			currentPage.value = page;
			await getOrders();
			$('.card__scroll').scrollTop(0);
			done();
		}
	});
}





export {
	getOrders,
	editOrder,
	pag,
	status,
	currentPage,
	perPage,
	lastPage,
	total,
}