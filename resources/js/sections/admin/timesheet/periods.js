export async function timesheetPeriodsCrud(getLastTimesheetPeriods = null, timesheetCrud = null, listType = null, regionId = null, timesheetCrudList = null, choosedPeriod = null, lastTimesheetPeriodsWaitBlock = null) {
	const viewsPath = 'admin.section.system.render.timesheet_periods';
	const sectionName = location.pathname.replace('/', '');
	
	const buttons = [];
	if (strpos(sectionName, 'accounting')) {
		buttons.push({id: 'accountingBuildBtn', action: 'accountingBuild', variant: 'yellow', title: 'Сформировать отчет', disabled: 1});
	}
	
	
	const {
		popper,
		wait,
		dialog,
		close,
		query,
		onScroll,
		disableButtons,
		enableButtons,
		setWidth
	} = await ddrPopup({
		url: 'crud/timesheet_periods/init',
		method: 'get',
		params: {views: viewsPath, orders_counts_stat: ['counts-stat'].includes(sectionName) ? 1 : 0},
		title: 'Периоды расписания',
		width: 600,
		buttons: ['ui.close', ...buttons],
	});
	
	wait();
	
	$.ddrCRUD({
		container: '#timesheetPeriodsList',
		itemToIndex: '[ddrtabletr]',
		route: 'crud/timesheet_periods',
		params: {
			list: {orders_counts_stat: ['counts-stat'].includes(sectionName) ? 1 : 0, accounting: strpos(sectionName, 'accounting') ? 1 : 0},
			//create: {orders_counts_stat: ['counts-stat'].includes(sectionName) ? 1 : 0},
			//edit: {guard: 'admin'}
			store: {orders_counts_stat: ['counts-stat'].includes(sectionName) ? 1 : 0, accounting: strpos(sectionName, 'accounting') ? 1 : 0},
		},
		viewsPath,
	}).then(({error, list, changeInputs, create, store, storeWithShow, edit, update, destroy, query, getParams, abort, remove}) => {
		
		wait(false);
		
		if (error) {
			console.log(error.message);
			//$.notify(error.message, 'error');
			$('#timesheetPeriodsTable').blockTable('error', error.message);
			return false;
		}
		
		$('#timesheetPeriodsTable').blockTable('buildTable');
		//wait(false);
		//enableButtons(true);
		changeInputs({'[save], [update]': 'enable', '[accountingperiod]': function(item) {
			let checkedCount = $(popper).find('[accountingperiod]:checked').length;
			$('#accountingBuildBtn').ddrInputs(checkedCount == 0 ? 'disable' : 'enable');
		}});
		
		
		
		
		
		
		
		
		
		//$('#contractAddBtn').ddrInputs('enable');
		
		$.timesheetPeriodsWinBuild = (btn, periodId, hasEvents) => {
			lastTimesheetPeriodsWaitBlock.on();
			$('#lastTimesheetPeriodsBlock').find('li').removeClass('active');
			$('#newTimesheetEventBtn, #importTimesheetEventsBtn, #exportOrdersBtn').setAttrib('hidden');
			choosedPeriod.value = periodId;
			ddrStore(`${sectionName}-choosedPeriod`, periodId);
			$('#searchOrdersField').ddrInputs('disable');
			
			if (_.isFunction(timesheetCrud)) {
				//timesheetCrud(periodId, listType);
				let buildOrdersTable = null;
				timesheetCrud(periodId, listType, regionId, buildOrdersTable, (list) => {
					timesheetCrudList.value = list;
					lastTimesheetPeriodsWaitBlock.off();
					if (hasEvents) $('#searchOrdersField').ddrInputs('enable');
				});
			}
			
			$('#lastTimesheetPeriodsBlock').find(`li[timesheetperiod="${periodId}"]`).addClass('active');
			close();
		}
		
		
		
		
		
		
		$.timesheetPeriodsAddBtnAction = (btn) => {
			let timesheetAddBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#ffffff91'
			});
			
			create((data, container, {error}) => {
				timesheetAddBtnWait.destroy();
				if (data) $(container).prepend(data);
				if (error) $.notify(error.message, 'error');
				$('#timesheetPeriodsTable').blockTable('buildTable');
			});
		}
		
		
		$.timesheetPeriodsSave = (btn) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			let timesheetSaveWait = $(row).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffffd6'
			});
			
			const lastTimesheetPeriodsWaitBlock = $('#lastTimesheetPeriodsBlock').ddrWait({
				iconHeight: '20px',
			});
			
			storeWithShow(row, (data, container, {error}) => {
				if (error) {
					timesheetSaveWait.destroy();
					$.notify(error.message, 'error');
				}
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
				
				if (data) {
					$(row).replaceWith(data);
					$.notify('Запись успешно сохранена!');
					$('#timesheetPeriodsTable').blockTable('buildTable');
					
					getLastTimesheetPeriods((periodsCounts) => {
						if (periodsCounts[choosedPeriod.value]) {
							$('#searchOrdersField').ddrInputs('enable');
						} else if (!$('#searchOrdersField').val()) {
							$('#searchOrdersField').ddrInputs('disable');
						}
						lastTimesheetPeriodsWaitBlock.destroy();
					}, choosedPeriod);
				}
			});
		}
		
		
		
		
		$.timesheetPeriodsRemove = (btn, id = null) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			if (!id) {
				remove(row);
			} else {
				dialog('Удалить период?', {
					buttons: {
						'Удалить|red': function({closeDialog}) {
							closeDialog();
							wait();
							let removePeriodWait = $(row).ddrWait({
								iconHeight: '15px',
								bgColor: '#ffffff91'
							});
							
							destroy(id, function(stat) {
								if (stat) {
									remove(row);
									$.notify('Период успешно удален!');
									
									if (choosedPeriod.value == id) {
										$('#timesheetContainer').html('<p class="color-gray-400 fz16px noselect text-center">Выберите период</p>');
										$('#listTypeChooser, #regionChooser').setAttrib('hidden');
										$('#newTimesheetEventBtn, #importTimesheetEventsBtn, #exportOrdersBtn').setAttrib('hidden');
										ddrStore(`${sectionName}-choosedPeriod`, false);
									}
									
									getLastTimesheetPeriods((periodsCounts) => {
										if (periodsCounts[choosedPeriod.value]) {
											$('#searchOrdersField').ddrInputs('enable');
										} else if (!$('#searchOrdersField').val()) {
											$('#searchOrdersField').ddrInputs('disable');
										}
										wait(false);
									}, choosedPeriod);
									
								} else {
									$.notify('Ошибка удаления периода!', 'error');
								} 
								wait(false);
								removePeriodWait.destroy();
							});
						},
						'Отмена|light': function({closeDialog}) {
							closeDialog();
						}
					}
				});
			}
		}
	
	});
	
	return {popper, wait, close};
}
