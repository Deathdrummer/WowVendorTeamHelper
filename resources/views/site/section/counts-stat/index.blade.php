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
	
	
	<div class="col-auto me-auto" teleport="#headerTeleport2">
		<div class="header__block">
			<div id="lastTimesheetPeriodsBlock" class="ml3rem minw-4rem maxw-35rem h6rem pt10px pb10px pr10px"></div>
		</div>
	</div>
	
	
	
	<div id="ordersCountsStatBlock" class="minh7rem"></div>
</section>













<script type="module">
	
	const sectionName = location.pathname.replace('/', '');
	const viewsPath = 'site.section.counts-stat.render.report';
	const listType = ref(ddrStore('listType') || 'actual');
	const regionId = ref(ddrStore('eventsRegion') || Number($('#regionChooser').find('[regionchooser]').attr('regionchooser')));
	const timesheetCrudList = ref(null);
	const choosedPeriod = ref(ddrStore(`${sectionName}-choosedPeriod`));
	let periodsWinFuncs;
	
	
	const {
		timesheetCrud,
		timesheetPeriodsCrud,
		getLastTimesheetPeriods,
		timesheetOrders,
		buildOrdersTable,
		orderCommentsChat,
		rawDataHistory,
		showStatusesTooltip
	} = await loadSectionScripts({section: 'timesheet', guard: 'admin'});
	
	
	
	
	const lastTimesheetPeriodsWaitBlock = $('#lastTimesheetPeriodsBlock').ddrWait({
		iconHeight: '26px',
	});
	
	
	$('#openTimesheetPeriodsBtn').ddrInputs('enable');
	$.openTimesheetPeriodsWin = async () => {
		periodsWinFuncs = await timesheetPeriodsCrud(getLastTimesheetPeriods, null, null, null, null, choosedPeriod, lastTimesheetPeriodsWaitBlock);
	}
	
	
	
	
	let isBuildesPeriod = false;
	$.timesheetPeriodsBuild = async (btn, periodId) => {
		if ($(btn).hasClass('active') || isBuildesPeriod) return;
		isBuildesPeriod = true;
		$('#lastTimesheetPeriodsBlock').find('li').removeClass('active');
		choosedPeriod.value = periodId;
		ddrStore(`${sectionName}-choosedPeriod`, periodId);
		$('#lastTimesheetPeriodsBlock').find(`[timesheetperiod="${periodId}"]`).addClass('active');
		periodsWinFuncs?.close();
		
		const timesheetPeriodsBuildWait = $('#ordersCountsStatBlock').ddrWait({
			iconHeight: '30px'
		});
		
		const {data, error, status, headers} = await ddrQuery.get('crud/timesheet/orders_counts_stat', {views: viewsPath, period_id: periodId});
		
		$('#ordersCountsStatBlock').html(data);
		
		timesheetPeriodsBuildWait.destroy();
		
		
		isBuildesPeriod = false;
		
		$(btn).addClass('active');
	}
	
	
	
	
	
	
	
	getLastTimesheetPeriods((periodsCounts) => {
		if (periodsCounts[choosedPeriod.value]) {
			$('#searchOrdersField').ddrInputs('enable');
		} else if (!$('#searchOrdersField').val()) {
			$('#searchOrdersField').ddrInputs('disable');
		}
		
		lastTimesheetPeriodsWaitBlock.off();
		if (choosedPeriod.value) $('#lastTimesheetPeriodsBlock').find(`[timesheetperiod="${choosedPeriod.value}"]`).addClass('active');
	});
	
	
	
	
	
	
	
	
</script>