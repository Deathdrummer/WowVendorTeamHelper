const viewsPath = 'site.section.confirm-orders.render';

export async function getOrders(type = 'actual') {
	const {data, error, status, headers} = await ddrQuery.get('crud/orders/confirmed', {views: viewsPath, type});
	
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	
	$('#confirmOrdersBlock').html(data);
	//$('#confirmOrdersBlock').blockTable('buildTable');
	
	
}