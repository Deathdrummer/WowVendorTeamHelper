const status = ref('new');
const currentPage = ref(1);
const lastPage = ref(null);
const perPage = ref(null);
const total = ref(null);



async function getOrders(ops = {}) {
	const {
		init,
	} = _.assign({
		init: false, // обновление списка
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
	pag,
	status,
	currentPage,
	perPage,
	lastPage,
	total,
}