<section>
	
	<div class="col-auto" teleport="#headerTeleport1">
		<div class="header__block">
			<div>
				<x-button
					size="large"
					variant="neutral"
					action="openTimesheetPeriodsWin"
					title="Периоды расписания"
					id="openTimesheetPeriodsBtn"
					disabled
					>
					<i class="fa-solid fa-fw fa-list-ul"></i>
				</x-button>
			</div>
		</div>
	</div>
	
	
	<div class="col-auto" teleport="#headerTeleport2">
		<div class="header__block">
			<div id="lastTimesheetPeriodsBlock" class="ml3rem minw-4rem maxw-35rem h6rem pt10px pb10px pr10px"></div>
		</div>
	</div>
	
	
	<div class="col-auto ms-auto" teleport="#headerTeleport3">
		<div class="header__block">
			<x-buttons-group size="large" gx="10">
				<x-button
					variant="yellow"
					action="exportOrdersAction"
					hidden
					title="Экспортировать заказы"
					id="exportOrdersBtn"
					>
					<i class="fa-solid fa-fw fa-file-export"></i>
				</x-button>
				
				<x-button
					variant="purple"
					action="importTimesheetEventsAction"
					hidden
					title="Импортировать события"
					id="importTimesheetEventsBtn"
					>
					<i class="fa-solid fa-fw fa-file-import"></i>
				</x-button>
				
				<x-button
					variant="green"
					action="newTimesheetEventAction"
					hidden
					title="Добавить событие"
					id="newTimesheetEventBtn"
					>
					<i class="fa-solid fa-fw fa-plus"></i>
				</x-button>
			</x-buttons-group>
		</div>
	</div>
	
	
	
	
	
	<x-chooser variant="neutral" size="normal" px="20" py="5" class="mb1rem" id="listTypeChooser" hidden>
		<x-chooser.item
			action="setListTypeAction:actual"
			active
			listtypechooser="actual"
			>Актуальные
		</x-chooser.item>
		<x-chooser.item
			action="setListTypeAction:past"
			listtypechooser="past"
			>Прошедшие
		</x-chooser.item>
	</x-chooser>
	
	
	<div id="timesheetContainer" class="timesheetcontainer pt2rem"><p class="color-gray-400 fz16px noselect text-center">Выберите период</p></div>
	
</section>








<script type="module">
	
	const listType = ref(ddrStore('listType') || 'actual');
	const timesheetCrudList = ref(null);
	const choosedPeriod = ref(ddrStore('choosedPeriod'));
	
	const {
		timesheetCrud,
		timesheetPeriodsCrud,
		getLastTimesheetPeriods,
		timesheetOrders,
		buildOrdersTable,
		orderCommentsChat,
		showStatusesTooltip
	} = await loadSectionScripts({section: 'timesheet', guard: 'admin'});
	
	//const viewsPath = 'admin.section.system.render.timesheet';
	
	
	
	
	const lastTimesheetPeriodsWaitBlock = $('#lastTimesheetPeriodsBlock').ddrWait({
		iconHeight: '26px',
	});
	
	
	
	$('#openTimesheetPeriodsBtn').ddrInputs('enable');
	$.openTimesheetPeriodsWin = async () => {
		timesheetPeriodsCrud(getLastTimesheetPeriods, timesheetCrud, listType, timesheetCrudList, choosedPeriod);
	}
	
	
	let isBuildesPeriod = false;
	$.timesheetPeriodsBuild = (btn, periodId) => {
		if ($(btn).hasClass('active') || isBuildesPeriod) return;
		let periodsBlockWait = $('#lastTimesheetPeriodsBlock').ddrWait({
			iconHeight: '25px',
			bgColor: '#ffffffdd'
		});
		isBuildesPeriod = true;
		$('#newTimesheetEventBtn, #importTimesheetEventsBtn, #exportOrdersBtn').setAttrib('hidden');
		$('#lastTimesheetPeriodsBlock').find('li').removeClass('active');
		choosedPeriod.value = periodId;
		ddrStore('choosedPeriod', periodId);
		
		timesheetCrud(periodId, listType, buildOrdersTable, (list) => {
			timesheetCrudList.value = list;
			isBuildesPeriod = false;
			periodsBlockWait.destroy();
		});
		$(btn).addClass('active');
	}
	
	
	
	
	$.timesheetGetOrders = (row, timesheetId) => {
		if ($(event.target).closest('[timesheetrulesblock]').length) return false;
		timesheetOrders(row, timesheetId);
	}
	
	
	
	$.setListTypeAction = (btn, isActive, type) => {
		if (isActive) return;
		
		const timesheetContainerWait = $('#timesheetContainer').ddrWait({
			iconHeight: '50px',
			text: 'Загрузка записей...'
		});
		
		listType.value = type;
		ddrStore('listType', type);
		timesheetCrudList.value({list_type: listType.value}, () => {
			$('#timesheetTable').blockTable('buildTable');
			timesheetContainerWait.destroy();
		});
	}
	
	
	
	$.openLink = (btn, url) => {
		if (!url) return;
		window.open(url, '_blank');
	}
	
	
	
	$.openCommentsWin = (btn, orderId, orderName) => {
		orderCommentsChat(orderId, orderName, btn);
	}
	
	
	$.openStatusesTooltip = (btn, orderId, timesheetId, status) => {
		showStatusesTooltip(btn, orderId, timesheetId, status);
	}
	
	
	
	$.openTimesheetCommentWin = async (btn, timesheetId) => {
		event.stopPropagation();
		
		const commentTextSelector = $(btn).closest('[ordercommentblock]').find('[rowcomment]');
		
		const {
			popper,
			wait,
			close,
			enableButtons,
		} = await ddrPopup({
			url: 'crud/timesheet/comment',
			method: 'get',
			params: {id: timesheetId, views: 'admin.section.system.render.timesheet'},
			title: 'Комментарий события', // заголовок
			width: '500', // ширина окна
			buttons: ['ui.close', {action: 'timesheetComentSave', title: 'Обновить'}],
			disabledButtons: true, // при старте все кнопки кроме закрытия будут disabled
		});
		
		enableButtons('close');
		
		const comment = $(popper).find('#timesheetComment');
		let commentStr = '';
		
		$(comment).ddrInputs('change:one', function(_, e) {
			enableButtons(true);
		});
		
		$(comment).ddrInputs('change', function(_, e) {
			commentStr = e?.target?.value || null;
		});
		
		
		
		
		$.timesheetComentSave = async () => {
			wait();
			const {data, error, headers} = await ddrQuery.post('crud/timesheet/comment', {
				id: timesheetId,
				comment: commentStr,
			});
			
			if (error) {
				$.notify('Ошибка обновления комментария!', 'error');
				console.log(error);
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(popper).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
				wait(false);
				return;
			}
			
			if (data) {
				if ($(commentTextSelector).children('p').length) {
					$(commentTextSelector).children('p').text(commentStr);
				} else {
					$(commentTextSelector).html(`<p class="fz12px lh900 format wodrbreak color-gray-500">${commentStr}</p>`);
				}
				
				close();
			}
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	getLastTimesheetPeriods(() => {
		lastTimesheetPeriodsWaitBlock.off();
		if (choosedPeriod.value) $('#lastTimesheetPeriodsBlock').find(`[timesheetperiod="${choosedPeriod.value}"]`).addClass('active');
	});
	
	
	// Автовыбор предыдущего выбранного периода
	if (choosedPeriod.value) {
		timesheetCrud(choosedPeriod.value, listType, buildOrdersTable, (list) => {
			timesheetCrudList.value = list;
			$('#listTypeChooser').find(`[listtypechooser]`).removeClass('chooser__item_active');
			$('#listTypeChooser').find(`[listtypechooser="${listType.value}"]`).addClass('chooser__item_active');
		});
	}
	
	
	
	
	
</script>