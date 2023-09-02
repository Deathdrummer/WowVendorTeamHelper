export async function confirmOrder(btn, id) {
	const {data, error, status, headers} = await ddrQuery.put('crud/orders/confirm', {order_id: id});
		
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	
	if (data) {
		let hasRows = !!$(btn).closest('[ddrtabletr]').siblings('[ddrtabletr]').length;
		if (hasRows) $(btn).closest('[ddrtabletr]').remove();
		else $(btn).closest('[ddrtable]').replaceWith('<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>');
		$.notify('Заказ успешно подтвежден!');
	}
}



export async function removeOrderFromConfirmed(btn, id, tsId, orderNumber) {
	const {data, error, status, headers} = await ddrQuery.delete('crud/orders/confirm', {order_id: id, timesheet_id: tsId});
		
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	
	if (data) {
		let hasRows = !!$(btn).closest('[ddrtabletr]').siblings('[ddrtabletr]').length;
		if (hasRows) $(btn).closest('[ddrtabletr]').remove();
		else $(btn).closest('[ddrtable]').replaceWith('<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>');
		$.notify('Заказ успешно удален!');
	}
}