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
	} else {
		$.notify('Ошибка! Заказ не подтвежден!', 'error');
	}
}



export async function confirmAllOrders(btn) {
	const {
		wait,
		close,
	} = await ddrPopup({
		width: 400,
		html: '<p class="color-green fz16px">Вы действительно хотите подтвердить все заказы?</p>',
		buttons: ['ui.close', {title: 'ui.confirm', action: 'confirmAllOrdersAct'}],
		buttonsAlign: 'center',
		centerMode: true,
	});	
	
	$.confirmAllOrdersAct = async () => {
		wait();
		const {data, error, status, headers} = await ddrQuery.put('crud/orders/confirm_all');
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			wait(false);
			return;
		}
		
		if (data) {
			$('#confirmOrdersBlock').html('<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>');
			$.notify('Все заказы успешно подтвеждены!');
			$(btn).remove();
			close();
		} else {
			$.notify('Ошибка! Заказы не подтвеждены!', 'error');
			close();
		}
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