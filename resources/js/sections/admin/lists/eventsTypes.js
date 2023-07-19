const viewsPath = 'admin.section.system.render.events_types';
	

export function eventsTypesCrud() {
	$.ddrCRUD({
		container: '#eventsTypesList',
		itemToIndex: '[ddrtabletr]',
		route: 'crud/events_types',
		params: {
			//list: {archive: 0/*department_id: deptId*/},
			//create: {guard: 'admin'},
			//edit: {guard: 'admin'}
			//store: {department_id: deptId},
		},
		viewsPath,
	}).then(({error, list, changeInputs, create, store, storeWithShow, edit, update, destroy, query, getParams, abort, remove}) => {
		
		if (error) {
			console.log(error.message);
			//$.notify(error.message, 'error');
			$('#eventsTypesCard').card('ready');
			$('#eventsTypesTable').blockTable('error', error.message);
			return false;
		}
		
		$('#eventsTypesTable').blockTable('buildTable');
		
		//wait(false);
		//enableButtons(true);
		changeInputs({'[save], [update]': 'enable'});
		
		
		$('#eventsTypesCard').card('ready');
		
		
		
		//$('#contractAddBtn').ddrInputs('enable');
		
		
		
		
		$.eventsTypesAddBtnAction = (btn) => {
			let eventsTypesAddBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#ffffff91'
			});
			
			create((data, container, {error}) => {
				eventsTypesAddBtnWait.destroy();
				if (data) $(container).append(data);
				if (error) $.notify(error.message, 'error');
				$('#eventsTypesTable').blockTable('buildTable');
			});
		}
		
		
		$.eventTypeSave = (btn) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			let eventTypeSaveWait = $(row).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffffd6'
			});
			
			storeWithShow(row, (data, container, {error}) => {
				if (data) {
					$(row).replaceWith(data);
					$.notify('Запись успешно сохранена!');
					$('#eventsTypesTable').blockTable('buildTable');
				}
				
				if (error) {
					eventTypeSaveWait.destroy();
					$.notify(error.message, 'error');
				} 
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		
		$.eventTypeUpdate = (btn, id) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			update(id, row, (data, container, {error}) => {
				if (data) {
					$(btn).ddrInputs('disable');
					$(row).find('input, select, textarea').ddrInputs('state', 'clear');
					$.notify('Запись успешно обновлена!');
					$('#eventsTypesTable').blockTable('buildTable');
				}
				
				if (error) $.notify(error.message, 'error');
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		
		$.eventTypeRemove = (btn, id = null) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			if (!id) {
				remove(row);
			} else {
				let removeeventTypePopup = ddrPopup({
					width: 400, // ширина окна
					lhtml: 'dialog.delete', // контент
					buttons: ['ui.cancel', {title: 'ui.delete', variant: 'red', action: 'roleRemoveAction'}],
					centerMode: true,
					winClass: 'ddrpopup_dialog color-red'
				});
				
				removeeventTypePopup.then(({close, wait}) => {
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
		
		
	
	});
}


	