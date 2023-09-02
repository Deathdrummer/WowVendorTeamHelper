<section>
	<x-chooser
		variant="neutral"
		size="normal"
		px="20"
		py="5"
		class="mb1rem"
		id="listTypeChooser"
		hidden
		>
		<x-chooser.item
			action="setListTypeAction:actual"
			active
			listtypechooser="actual"
			>Актуальные
		</x-chooser.item>
		<x-chooser.item
			action="setListTypeAction:past"
			listtypechooser="past"
			>Подтвержденные
		</x-chooser.item>
	</x-chooser>
	
	

	<div id="confirmOrdersBlock" class="mt2rem minh7rem"></div>
</section>













<script type="module">
	const {getOrders, confirmOrder, removeOrderFromConfirmed} = await loadSectionScripts({section: 'confirm-orders', guard: 'site'});
	const {orderCommentsChat} = await loadSectionScripts({section: 'timesheet', guard: 'admin'});
	
	const listTypeBlockWait = $('#confirmOrdersBlock').ddrWait({
		iconHeight: '30px',
		bgColor: '#f8f9fbbd'
	});
	
	await getOrders('actual');
	$('#listTypeChooser').removeAttrib('hidden');
	
	listTypeBlockWait.destroy();
	
	
	
	$.setListTypeAction = async (btn, isActive, type) => {
		if (isActive) return false;
		
		$('#listTypeChooser').setAttrib('disabled');
		const listTypeBlockWait = $('#confirmOrdersBlock').ddrWait({
			iconHeight: '30px',
			bgColor: '#f8f9fbbd'
		});
		
		await getOrders(type);
		
		$('#listTypeChooser').removeAttrib('disabled');
		listTypeBlockWait.destroy();
	}
	
	
	$.confirmOrderAction = (btn, id) => {
		confirmOrder(btn, id);
		//console.log(btn, id);
	}
	
	
	$.removeConfirmedOrder = (btn, id, orderNumber) => {
		removeOrderFromConfirmed(btn, id, orderNumber);
	}
	
	$.openCommentsWin = (btn, orderId, orderName) => {
		orderCommentsChat(orderId, orderName, btn);
	}
	
	$.openLink = (btn, url) => {
		if (!url) return;
		window.open(url, '_blank');
	}
	
</script>