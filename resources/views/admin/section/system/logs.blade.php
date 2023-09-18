<section>
	<x-card
		id="eventLogContainer"
		loading
		ready
		style="height: calc(100vh - 168px);"
		>
		
		<div class="ddrtabs">
			<div class="ddrtabs__nav b16rem fz14px">
				<ul class="ddrtabsnav" ddrtabsnav id="logTabs">
					<li class="ddrtabsnav__item ddrtabsnav__item_active" onclick="$.getEventsLogsList(event, 1, this.classList.contains('ddrtabsnav__item_active'))" ddrtabsitem>События</li>
					<li class="ddrtabsnav__item" onclick="$.getEventsLogsList(event, 2, this.classList.contains('ddrtabsnav__item_active'))" ddrtabsitem>Заказы</li>
				</ul>
			</div>
			
			<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
				<div class="ddrtabscontent__item ddrtabscontent__item_visible" id="logList" ddrtabscontentitem="sectionsTab1"></div>
			</div>
		</div>
	</x-card>
	
	<div class="w100 text-end">
		<div id="pagLog"></div>
	</div>
</section>




<script type="module">
	
	const hData = ref({});
	const group = ref(1);
	await getEventsList();
	
	$.getEventsLogsList = async (event, grp, isActive) => {
		event.preventDefault();
		if (isActive) return false;
		group.value = grp;
		await getEventsList(group.value);
	}

	//'current_page'	
	//'per_page'		
	//'last_page'		
	//'total'			
	
	const {pagRefresh} = $('#pagLog').ddrPagination({
		countPages: hData.value['last_page'],
		//currentPage: 3,
		itemsAround: 1,
		async onChangePage(page, done) {
			await getEventsList(group.value, page, true);
			//$('.card__scroll').scrollTop(0);
			done();
		}
	});
	
	
	
	async function getEventsList(group = 1, page = 1, rereshPag = false) {
		const eventLogWait = $('#logList').ddrWait();
		$('#logTabs').addClass('notouch');
		const {data, error, status, headers} = await ddrQuery.get('crud/events_logs', {group, page});
		
		hData.value = headers;
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
		}
		
		
		if (data) {
			$('#logList').html(data);
			$('#eventLogTable').blockTable('buildTable');
		}
		
		eventLogWait.destroy();
		$('#logTabs').removeClass('notouch');
		/*if (rereshPag) pagRefresh({
			countPages: hData.value['last_page'],
		});*/
	}
	
	
	
	$.eventsLogsInfo = async (btn, logId) => {
		const {
			state,
			popper,
			wait,
			setTitle,
			setButtons,
			loadData,
			setHtml,
			setLHtml,
			dialog,
			close,
			onClose,
			onScroll,
			disableButtons,
			enableButtons,
			setWidth,
		} = await ddrPopup({
			//url: 'crud/event_log',
			//method: 'get',
			//params: {id: logId},
			title: 'Подробная информация',
			width: 800,
			buttons: ['ui.close'],
		});
		
		wait();
		
		const {data, error, status, headers} = await ddrQuery.get('crud/event_log', {id: logId});
		
		setWidth(headers['hasupdated'] ? 1200 : 800);
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		

		setHtml(data);
		wait(false);
	}
	
	
	
	
</script>