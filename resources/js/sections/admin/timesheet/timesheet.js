const viewsPath = 'admin.section.system.render.timesheet';
	

export async function timesheetCrud(periodId = null, listType = null, buildOrdersTable = null, cb = null) {
	const timesheetContainerWait = $('#timesheetContainer').ddrWait({
		iconHeight: '50px',
		text: 'Загрузка записей...'
	});
	
	const {data, error, status, headers} = await ddrQuery.get('crud/timesheet/init', {views: viewsPath});
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		timesheetContainerWait.destroy();
		return;
	}
	
	$('#timesheetContainer').html(data);
	
	timesheetContainerWait.destroy();
	
	
	
	$.ddrCRUD({
		container: '#timesheetList',
		itemToIndex: '[ddrtabletr]',
		route: 'crud/timesheet',
		params: {
			list: {period_id: periodId, list_type: listType.value},
			//create: {period_id: periodId},
			store: {timesheet_period_id: periodId},
			edit: {period_id: periodId},
			update: {list_type: '34534534'},
			//destroy: {period_id: periodId},
		},
		/*globalParams: {
			period_id: periodId
		},*/
		viewsPath,
	}).then(({error, list, changeInputs, create, store, storeWithShow, edit, update, destroy, query, getParams, abort, remove}) => {
		
		if (error) {
			console.log(error.message);
			//$.notify(error.message, 'error');
			$('#timesheetTable').blockTable('error', error.message);
			return false;
		}
		
		callFunc(cb, list);
		
		$('#listTypeChooser').removeAttrib('hidden');
		
		$('#timesheetTable').blockTable('buildTable');
		//wait(false);
		//enableButtons(true);
		//changeInputs({'[save], [update]': 'enable'});
		
		$('#newTimesheetEventBtn, #importTimesheetEventsBtn, #exportOrdersBtn').removeAttrib('hidden');
		
		
		
		
		$.newTimesheetEventAction = async (btn) => {
			let timesheetAddBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#38ce3c91'
			});
			
			const {
				popper,
				wait,
				setHtml,
				close,
				enableButtons,
			} = await ddrPopup({
				title: 'Новое событие', // заголовок
				width: 350, // ширина окна
				buttons: ['ui.close', {action: 'timesheetSave', title: 'Добавить'}],
				disabledButtons: true,
			});
			
			wait();
			
			create((data, container, {error}) => {
				timesheetAddBtnWait.destroy();
				wait(false);
				
				if (error) {
					$.notify(error.message, 'error');
					return;
				}
				
				if (data) {
					setHtml(data, {}, () => {
						enableButtons('close');
						$(popper).ddrInputs('change', () => {
							enableButtons(true);
						});
					});	
				} 
			});
			
			
			$.timesheetSave = (btn) => {
				let form = $(popper);
				
				wait();
				
				store(form, (data, container, {error}) => {
					if (error) {
						wait(false);
						$.notify(error.message, 'error');
						console.log(error);
						
						if (error.errors) {
							$.each(error.errors, function(field, errors) {
								$(form).find('[name="'+field+'"]').ddrInputs('error', $(form).find('[name="'+field+'"]').attr('errortext') || errors[0]);
							});
						}
						return;
					} 
					
					if (data) {
						list({list_type: listType.value}, () => {
							$('#timesheetTable').blockTable('buildTable');
							$.notify('Запись успешно добавлена!');
							
							incrementLastPeriodCount(periodId);
							
							close();
						});
					}
				});
			}
		}
		
		
		
		
		
		
		
		
		$.timesheetEdit = async (btn, id) => {
			let timesheetEditBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#38ce3c91'
			});
			
			const {
				popper,
				wait,
				setHtml,
				close,
				enableButtons,
			} = await ddrPopup({
				title: 'Изменить событие', // заголовок
				width: 350, // ширина окна
				disabledButtons: true,
				buttons: ['ui.close', {action: 'timesheetUpdate', title: 'Обновить'}],
			});
			
			wait();
			
			
			
			
			edit(id, (data, container, {error, status, headers}) => {
				timesheetEditBtnWait.destroy();
				wait(false);
				if (error) {
					$.notify(error.message, 'error');
					return;
				} 
				if (data) setHtml(data, {}, () => {
					enableButtons('close');
					$(popper).ddrInputs('change', () => {
						enableButtons(true);
					});
				});	
			});
			
			
			
			$.timesheetUpdate = async (btn, __) => {
				let form = $(popper);
					
				update(id, form, (data, container, {error}) => {
					if (error) {
						wait(false);
						$.notify(error.message, 'error');
						console.log(error);
						
						if (error.errors) {
							$.each(error.errors, function(field, errors) {
								$(form).find('[name="'+field+'"]').ddrInputs('error', $(form).find('[name="'+field+'"]').attr('errortext') || errors[0]);
							});
						}
						return;
					} 
					
					if (data) {
						list({list_type: listType.value}, () => {
							$('#timesheetTable').blockTable('buildTable');
							$.notify('Запись успешно изменена!');
							close();
						});
					}
				});
			}
		}
		
		
		
		
		
		
		$.timesheetRemove = (btn, id = null) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			if (!id) {
				remove(row);
			} else {
				let removeTimesheetPopup = ddrPopup({
					width: 400, // ширина окна
					lhtml: 'dialog.delete', // контент
					buttons: ['ui.cancel', {title: 'ui.delete', variant: 'red', action: 'roleRemoveAction'}],
					centerMode: true,
					winClass: 'ddrpopup_dialog color-red'
				});
				
				removeTimesheetPopup.then(({close, wait}) => {
					$.roleRemoveAction = (btn) => {
						wait();
						destroy(id, function(stat) {
							if (stat) {
								remove(row);
								$.notify('Запись успешно удалена!');
							} else {
								$.notify('Ошибка удаления записи!', 'error');
							} 
							close();
						});
					}
				});	
			}
		}
		
		
		
		
		
		
		
		
		$.timesheetNewOrder = async (btn, timesheet_id = null) => {
			const {
				popper,
				wait,
				setHtml,
				close,
				enableButtons,
			} = await ddrPopup({
				url: 'crud/orders/form',
				method: 'get',
				params: {timesheet_id, views: 'admin.section.system.render.orders', action: 'new'},
				title: 'Новый заказ', // заголовок
				width: 600, // ширина окна
				disabledButtons: true,
				buttons: ['ui.close', {action: 'timesheetAddOrder', title: 'Добавить'}],
			});
			
			enableButtons('close');
			
			$('#orderFormPrice').number(true, 2, '.', ' ');
			
			$(popper).ddrInputs('change:one', () => {
				enableButtons(true);
			});
			
			
			$.timesheetAddOrder = async () => {
				wait();
				const formData = $(popper).ddrForm({timesheet_id});
				
				const {data, error, status, headers} = await ddrQuery.post('crud/orders/form', formData);
				
				wait(false);
				
				if (error) {
					$.notify('Ошибка добавления заказа!', 'error');
					console.log(error);
					if (error.errors) {
						$.each(error.errors, function(field, errors) {
							$(popper).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
						});
					}
					return;
				}
				
				if (data) {
					$.notify('Заказ успешно добавлен!');
					
					if ($(btn).closest('[ddrtabletr]').hasAttr('opened')) {
						buildOrdersTable($(btn).closest('[ddrtabletr]'), timesheet_id, () => {
							incrementTimesheetCount(btn, timesheet_id);
						});
					} else {
						incrementTimesheetCount(btn, timesheet_id);
					}
					close();
				}
			}
		}
		
		
		
	
		
		
		
		
		
		$.importTimesheetEventsAction = async (btn) => {
			const timesheetImportBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#b08ad791'
			});
			
			const {
				popper,
				wait,
				setHtml,
				close,
				enableButtons,
				disableButtons,
			} = await ddrPopup({
				title: 'Импорт событий', // заголовок
				url: 'crud/timesheet/import_form',
				method: 'get',
				params: {
					views: viewsPath,
					section: 'system.timesheet' // это для загрузки настроек
				},
				width: 500, // ширина окна
				buttons: ['ui.close', {action: 'setTimesheetImport', title: 'Импортировать'}],
				disabledButtons: true,
			});
			
			timesheetImportBtnWait.destroy();
			enableButtons('close');
			
			
			let importTSWait;
			const {getFile} = $('#importTSEvent').ddrFiles('choose', {
				extensions: ['txt'],
				init({count}) {
					importTSWait = $('#importTSEvent').ddrWait({
						iconHeight: '20px',
						bgColor: '#b08ad791'
					});
				},
				preload({key, iter}) {},
				error({text, extensions, file}) {
					if (text == 'forbidden_extension') {
						$.notify(`Доступен только текстовый формат ${extensions.join(', ')}`, 'error');
					}
					importTSWait.destroy();
				},
				callback({file, name, ext, key, size, type, preview}, {done, index}) {
					$('#importTSEventFileName').text(name);
					//if ($('#importEventsSeparator').val()) enableButtons(true);
					enableButtons(true);
				},
				done({files}) {
					importTSWait.destroy();
				},
			});
			
			
			$.setTimesheetImport = async () => {
				wait();
				let file = getFile();
					//separator = $('#importEventsSeparator').val();
				
				const {data, error, status, headers} = await ddrQuery.post('crud/timesheet/import', {period_id: periodId, file});
				
				if (error) {
					$.notify('Ошибка импорта событий!', 'error');
					wait(false);
					return;
				}
				
				list({list_type: listType.value}, () => {
					$('#timesheetTable').blockTable('buildTable');
					$.notify('События успешно импортированы!');
					incrementLastPeriodCount(periodId, data);
					close();
				});
				
			}
			
		}
		
		
		
		
		
		
		
		
		
		$.exportOrdersAction = async (btn) => {
			
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
				url: 'crud/timesheet/export',
				method: 'get',
				params: {views: viewsPath},
				title: 'Экспорт заказов',
				width: 600,
				buttons: ['ui.close', {action: 'setTimesheetExport', title: 'Экспорт'}],
				//disabledButtons: true,
			});
			
			enableButtons('close');
			
			let activeTab = 'all';
			$.exportOrdersType = (btn, isActive, type) => {
				if (isActive) return;
				
				activeTab = type;
				
				$('[exportordersform]:visible').setAttrib('hidden');
				$(`[exportordersform="${activeTab}"]`).removeAttrib('hidden');
			}
			
			
			
			
			
			
			$.setTimesheetExport = async () => {
				
				const formBlock = $(`[exportordersform="${activeTab}"]`);
				let stat = true;
				
				
				if (!$(formBlock).find('[name="date_from"]').val()) {
					$('#exportOrdersDateFrom').ddrInputs('error', 'Необходимо выбрать дату!');
					stat = false;
				} 
				
				if (!$(formBlock).find('[name="date_to"]').val()) {
					$('#exportOrdersDateTo').ddrInputs('error', 'Необходимо выбрать дату!');
					stat = false;
				} 
				
				if (!stat) return;
				
				wait();
				
				const formData = $(formBlock).ddrForm({type: activeTab});
				
				const {data, error, status, headers} = await ddrQuery.post('crud/timesheet/export', formData, {responseType: 'blob'});
				
				$.ddrFiles('export', {
					data,
					headers
				});
				
				wait(false);
				//close();
				
			}
			
			
			/*const timesheetExportBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#3e4b5b96'
			});
			
			
			
			if (error) {
				timesheetExportBtnWait.destroy();
				$.notify('Ошибка экспорта заказов!', 'error');
				return false;
			}
			
			$.ddrFiles('export', {
				data,
				headers
			});
			
			timesheetExportBtnWait.destroy();*/
		}
		
		
		
		
	});
}








function incrementLastPeriodCount(periodId = null, countEvents = 1) {
	if (_.isNull(periodId)) console.error('incrementLastPeriodCount ошибка -> не передан periodId');
	let count = $('#lastTimesheetPeriodsBlock').find(`li[timesheetperiod="${periodId}"] [timesheetperiodscounter]`).text();
	$('#lastTimesheetPeriodsBlock').find(`li[timesheetperiod="${periodId}"] [timesheetperiodscounter]`).text(Number(count) + countEvents);
}



function incrementTimesheetCount(btn = null, timesheetId = null) {
	if (_.isNull(timesheetId)) console.error('incrementTimesheetCount ошибка -> не передан timesheetId');
	let count = $(btn).closest('[ddrtabletr]').find('[orderscount]').text();
	$(btn).closest('[ddrtabletr]').find('[orderscount]').text(Number(count) + 1);
}


	