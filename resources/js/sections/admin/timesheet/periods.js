const viewsPath = 'admin.section.system.render.timesheet_periods';
	

export async function timesheetPeriodsCrud(getLastTimesheetPeriods = null, timesheetCrud = null, listType = null, timesheetCrudList = null, choosedPeriod = null) {
	
	const {
		state, // isClosed
		popper,
		wait,
		setTitle,
		setButtons,
		loadData,
		setHtml,
		setLHtml,
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
		params: {views: viewsPath},
		title: 'Периоды расписания',
		width: 600,
		//frameOnly, // Загрузить только каркас
		//html, // контент
		//lhtml, // контент из языковых файлов
		buttons: ['ui.close'/*, {action: 'tesTest', title: 'Просто кнопка'}*/],
		//buttonsAlign, // выравнивание вправо
		//disabledButtons, // при старте все кнопки кроме закрытия будут disabled
		//closeByBackdrop, // Закрывать окно только по кнопкам [ddrpopupclose]
		//changeWidthAnimationDuration, // ms
		//buttonsGroup, // группа для кнопок
		//winClass: 'h60vh', // добавить класс к модальному окну
		//centerMode, // контент по центру
		//topClose // верхняя кнопка закрыть
	});
	
	wait();
	

	
	
	$.ddrCRUD({
		container: '#timesheetPeriodsList',
		itemToIndex: '[ddrtabletr]',
		route: 'crud/timesheet_periods',
		params: {
			//list: {archive: 0/*department_id: deptId*/},
			//create: {guard: 'admin'},
			//edit: {guard: 'admin'}
			//store: {department_id: deptId},
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
		changeInputs({'[save], [update]': 'enable'});
		
		
		
		//$('#contractAddBtn').ddrInputs('enable');
		
		
		
		$.timesheetPeriodsWinBuild = (btn, periodId) => {
			$('#lastTimesheetPeriodsBlock').find('li').removeClass('active');
			$('#newTimesheetEventBtn').setAttrib('hidden');
			choosedPeriod.value = periodId;
			
			if (_.isFunction(timesheetCrud)) {
				//timesheetCrud(periodId, listType);
				let buildOrdersTable = null;
				timesheetCrud(periodId, listType, buildOrdersTable, (list) => {
					timesheetCrudList.value = list;
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
					
					getLastTimesheetPeriods(() => {
						lastTimesheetPeriodsWaitBlock.destroy();
					});
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
										getLastTimesheetPeriods(() => {
											wait(false);
										});
										$('#timesheetContainer').html('<p class="color-gray-400 fz16px noselect text-center">Выберите период</p>');
										$('#listTypeChooser').setAttrib('hidden');
										$('#newTimesheetEventBtn').setAttrib('hidden');
									}
									
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
	
	
}
