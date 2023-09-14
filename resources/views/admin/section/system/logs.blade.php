<section>
	<x-card
		id="eventLogContainer"
		loading
		ready
		style="height: calc(100vh - 168px);"
		>
		
		<div class="ddrtabs">
			<div class="ddrtabs__nav b16rem fz14px">
				<ul class="ddrtabsnav" ddrtabsnav>
					<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="sectionsTab1">События</li>
					<li class="ddrtabsnav__item" ddrtabsitem="sectionsTab2">Заказы</li>
				</ul>
			</div>
			
			{{--  --}}
			<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
				<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="sectionsTab1">
					<x-table id="eventLogTable" class="w100" scrolled="calc(100vh - 224px)" noborder>
						<x-table.head>
							<x-table.tr class="h4rem">
								<x-table.td class="w16rem v-end" noborder><strong class="fz12px lh90">Пользователь</strong></x-table.td>
								<x-table.td class="w16rem v-end" noborder><strong class="fz12px lh90">Тип события</strong></x-table.td>
								<x-table.td class="w-7rem v-end" noborder><strong class="fz12px lh90">ID события</strong></x-table.td>
								<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">Период</strong></x-table.td>
								<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">Команда</strong></x-table.td>
								<x-table.td class="w-20rem v-end" noborder><strong class="fz12px lh90">Тип события</strong></x-table.td>
								<x-table.td class="w-auto v-end" noborder><strong class="fz12px lh90">Дата и время события</strong></x-table.td>
								<x-table.td class="w18rem v-end" noborder><strong class="fz12px lh90">Дата и время</strong></x-table.td>
							</x-table.tr>
						</x-table.head>
						<x-table.body id="logList"></x-table.body>
					</x-table>
				</div>
				<div class="ddrtabscontent__item" ddrtabscontentitem="sectionsTab2"></div>
			</div>
		</div>
	</x-card>
	
	<div class="w100 text-end">
		<div id="pagLog"></div>
	</div>
</section>




<script type="module">
	
	const hData = ref({});
	await getEventsList();

	//'current_page'	
	//'per_page'		
	//'last_page'		
	//'total'			
	
	const {pagRefresh} = $('#pagLog').ddrPagination({
		countPages: hData.value['last_page'],
		//currentPage: 3,
		itemsAround: 1,
		async onChangePage(page, done) {
			await getEventsList(page, true);
			//$('.card__scroll').scrollTop(0);
			done();
		}
	});
	
	
	
	async function getEventsList(page = 1, rereshPag = false) {
		const eventLogWait = $('#eventLogContainer').ddrWait();
		
		const {data, error, status, headers} = await ddrQuery.get('crud/events_logs', {page});
		
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
		
		/*if (rereshPag) pagRefresh({
			countPages: hData.value['last_page'],
		});*/
	}
	
	
	
</script>