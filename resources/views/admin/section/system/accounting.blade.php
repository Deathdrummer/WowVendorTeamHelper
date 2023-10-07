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
	
	
	<div id="accountingBlock" class="minh7rem"></div>
</section>







<script type="module">
	const sectionName = location.pathname.replace('/', '');
	const viewsPath = 'admin.section.system.render.accounting';
	const choosedPeriod = ref(null); // ref(ddrStore(`${sectionName}-choosedPeriod`));
	let periodsWinFuncs;
	
	
	const {
		timesheetPeriodsCrud,
		getLastTimesheetPeriods,
	} = await loadSectionScripts({section: 'timesheet', guard: 'admin'});
	
	
	
	const ordersCountsStatWaitBlock = $('#lastTimesheetPeriodsBlock').ddrWait({
		iconHeight: '26px',
	});
	
	
	$('#openTimesheetPeriodsBtn').ddrInputs('enable');
	$.openTimesheetPeriodsWin = async () => {
		periodsWinFuncs = await timesheetPeriodsCrud(getLastTimesheetPeriods, null, null, null, null, choosedPeriod, ordersCountsStatWaitBlock);
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
		
		const timesheetPeriodsBuildWait = $('#accountingBlock').ddrWait({
			iconHeight: '30px'
		});
		
		const {data, error, status, headers} = await ddrQuery.get('crud/accounting', {views: viewsPath, periods_ids: [periodId]});
		
		$('#accountingBlock').html(data);
		
		timesheetPeriodsBuildWait.destroy();
		
		
		isBuildesPeriod = false;
		
		$(btn).addClass('active');
	}
	
	
	
	
	$.accountingBuild = async (btn) =>{
		const choosedChecks = $(periodsWinFuncs?.popper).find('[accountingperiod]:checked');
		
		const choosedPeriods = [];
		for (const check of choosedChecks) {
			choosedPeriods.push(Number($(check).attr('accountingperiod')));
		}
		
		const timesheetPeriodsBuildWait = $('#accountingBlock').ddrWait({
			iconHeight: '30px'
		});
		
		const {data, error, status, headers} = await ddrQuery.get('crud/accounting', {views: viewsPath, periods_ids: choosedPeriods});
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		
		$('#accountingBlock').html(data);
		
		timesheetPeriodsBuildWait.destroy();
		
		periodsWinFuncs?.close();
		console.log(choosedPeriods);
		
		//for item of choosedPeriods
		//console.log(choosedPeriods);
	}
	
	
	
	getLastTimesheetPeriods((periodsCounts) => {
		if (periodsCounts[choosedPeriod.value]) {
			$('#searchOrdersField').ddrInputs('enable');
		} else if (!$('#searchOrdersField').val()) {
			$('#searchOrdersField').ddrInputs('disable');
		}
		
		ordersCountsStatWaitBlock.off();
		if (choosedPeriod.value) $('#lastTimesheetPeriodsBlock').find(`[timesheetperiod="${choosedPeriod.value}"]`).addClass('active');
	});
</script>