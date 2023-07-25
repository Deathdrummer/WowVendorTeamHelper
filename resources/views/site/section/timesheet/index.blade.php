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
			<div id="lastTimesheetPeriodsBlock" class="ml3rem minw-4rem maxw-15rem h4rem"></div>
		</div>
	</div>
	
	
	<div class="col-auto ms-auto" teleport="#headerTeleport3">
		<div class="header__block">
			<div>
				<x-button
					size="large"
					variant="green"
					action="newTimesheetEventAction"
					hidden
					title="Добавить событие"
					id="newTimesheetEventBtn"
					>
					<i class="fa-solid fa-fw fa-plus"></i>
				</x-button>
			</div>
		</div>
	</div>
	
	
	
	
	
	<x-chooser variant="neutral" size="normal" px="20" py="5" class="mb1rem" id="listTypeChooser" hidden>
		<x-chooser.item
			id="chooserAll"
			action="setListTypeAction:actual"
			active
			>Актуальные
		</x-chooser.item>
		<x-chooser.item
			id="chooserAll"
			action="setListTypeAction:past"
			>Прошедшие
		</x-chooser.item>
	</x-chooser>
	
	
	<div id="timesheetContainer" class="timesheetcontainer pt2rem"><p class="color-gray-400 fz16px noselect text-center">Выберите период</p></div>
	
</section>








<script type="module">
	
	const listType = ref('actual');
	const timesheetCrudList = ref(null);
	const choosedPeriod = ref(null);
	
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
	
	//timesheetCrud();
	
	
	
	
	$('#openTimesheetPeriodsBtn').ddrInputs('enable');
	$.openTimesheetPeriodsWin = async () => {
		timesheetPeriodsCrud(getLastTimesheetPeriods, timesheetCrud, listType, timesheetCrudList, choosedPeriod);
	}
	
	
	
	$.timesheetPeriodsBuild = (btn, periodId) => {
		if ($(btn).hasClass('active')) return;
		$('#newTimesheetEventBtn').setAttrib('hidden');
		$('#lastTimesheetPeriodsBlock').find('li').removeClass('active');
		choosedPeriod.value = periodId;
		
		timesheetCrud(periodId, listType, buildOrdersTable, (list) => {
			timesheetCrudList.value = list;
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
	
	
	getLastTimesheetPeriods(() => {
		lastTimesheetPeriodsWaitBlock.off();
	});
	
	
	
	
	
	
	
</script>