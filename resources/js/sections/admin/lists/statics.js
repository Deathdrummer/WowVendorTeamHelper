const viewsPath = 'admin.section.system.render.statics';
	

export function staticsCrud() {
	$.ddrCRUD({
		container: '#staticsList',
		itemToIndex: '[ddrtabletr]',
		route: 'crud/statics',
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
			$('#staticsCard').card('ready');
			$('#staticsTable').blockTable('error', error.message);
			return false;
		}
		
		
		$('#staticsTable').blockTable('buildTable');
		//wait(false);
		//enableButtons(true);
		changeInputs({'[save], [update]': 'enable'});
		
		
		$('#staticsCard').card('ready');
		
		
		//$('#contractAddBtn').ddrInputs('enable');
		
		
		
		
		$.commandsAddBtnAction = (btn) => {
			let commandsAddBtnWait = $(btn).ddrWait({
				iconHeight: '20px',
				bgColor: '#ffffff91'
			});
			
			create((data, container, {error}) => {
				commandsAddBtnWait.destroy();
				if (data) $(container).append(data);
				if (error) $.notify(error.message, 'error');
				$('#staticsTable').blockTable('buildTable');
			});
		}
		
		
		$.commandSave = (btn) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			let commandSaveWait = $(row).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffffd6'
			});
			
			storeWithShow(row, (data, container, {error}) => {
				if (data) {
					$(row).replaceWith(data);
					$.notify('Запись успешно сохранена!');
					$('#staticsTable').blockTable('buildTable');
				}
				
				if (error) {
					commandSaveWait.destroy();
					$.notify(error.message, 'error');
				} 
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		
		$.commandUpdate = (btn, id) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			update(id, row, (data, container, {error}) => {
				if (data) {
					$(btn).ddrInputs('disable');
					$(row).find('input, select, textarea').ddrInputs('state', 'clear');
					$.notify('Запись успешно обновлена!');
					$('#staticsTable').blockTable('buildTable');
				}
				
				if (error) $.notify(error.message, 'error');
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		
		$.commandRemove = (btn, id = null) => {
			let row = $(btn).closest('[ddrtabletr]');
			
			if (!id) {
				remove(row);
			} else {
				let removeCommandPopup = ddrPopup({
					width: 400, // ширина окна
					lhtml: 'dialog.delete', // контент
					buttons: ['ui.cancel', {title: 'ui.delete', variant: 'red', action: 'roleRemoveAction'}],
					centerMode: true,
					winClass: 'ddrpopup_dialog color-red'
				});
				
				removeCommandPopup.then(({close, wait}) => {
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


	