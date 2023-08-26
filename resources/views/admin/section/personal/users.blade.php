<section>
	<x-card id="usersCard" loading>
		<div class="table">
			<table class="w100">
				<thead>
					<tr>
						<td class="w30rem"><strong>Имя сотрудника</strong></td>
						<td class="w30rem"><strong>Имя сотрудника (только для админа)</strong></td>
						<td class="w30rem"><strong>E-mail</strong></td>
						<td class="w28rem"><strong>Роль</strong></td>
						<td></td>
						<td class="w7rem center" title="Верифицирован"><strong>Вериф.</strong></td>
						{{-- <td class="w7rem center" title="Выслать доступ сотруднику"><strong>Высл. доступ.</strong></td>
						<td class="w7rem center"><strong>Права</strong></td> --}}
						<td class="w20rem center"><strong>Действия</strong></td>
					</tr>
				</thead>
				<tbody id="usersList"></tbody>
				<tfoot>
					<tr>
						<td colspan="9" class="right">
							<x-button id="usersAddBtn" variant="blue" group="normal" px="15" disabled>Добавить</x-button>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</x-card>
</section>




<script type="module">
	
	
	$.ddrCRUD({
		container: '#usersList',
		itemToIndex: 'tr',
		route: 'ajax/users',
		viewsPath: 'admin.section.personal.render.users',
		//onInit(container) {},
	}).then(({error, list, changeInputs, create, store, storeWithShow, update, destroy, remove, query, viewsPath}) => {
		
		$('#usersCard').card('ready');
		
		if (error) {
			$.notify(error.message, 'error');
			return false;
		}  
		
		$('#usersAddBtn').ddrInputs('enable');
		
		changeInputs({'[save], [update]': 'enable'});
		
		
		$('#usersAddBtn').on(tapEvent, function() {
			let usersAddBtnWait = $(this).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffff91'
			});
			
			create((data, container, {error}) => {
				usersAddBtnWait.destroy();
				if (data) $(container).append(data);
				if (error) $.notify(error.message, 'error');
			});
		});
		
		
		$.usersSave = (btn) => {
			let row = $(btn).closest('tr');
			
			let usersSaveWait = $(row).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffffd6'
			});
			
			storeWithShow(row, (data, container, {error}) => {
				if (data) {
					$(row).replaceWith(data);
					$.notify('Запись успешно сохранена!');
				}
				
				if (error) {
					usersSaveWait.destroy();
					$.notify(error.message, 'error');
				} 
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		$.usersUpdate = (btn) => {
			let id = $(btn).attr('update'),
				row = $(btn).closest('tr');
			
			update(id, row, (data, container, {error}) => {
				if (data) {
					$(row).find('[update]').ddrInputs('disable');
					$(row).find('input, select, textarea').ddrInputs('state', 'clear');
					$.notify('Запись успешно обновлена!');
				}
				
				if (error) $.notify(error.message, 'error');
				
				if (error.errors) {
					$.each(error.errors, function(field, errors) {
						$(row).find('[name="'+field+'"]').ddrInputs('error', errors[0]);
					});
				}
			});
		}
		
		
		
		$.usersRemove = (btn) => {
			let id = $(btn).attr('remove'),
				row = $(btn).closest('tr');
			
			if (!id) {
				remove(row);
			} else {
				let removeUsersPopup = ddrPopup({
					width: 400, // ширина окна
					lhtml: 'dialog.delete', // контент
					buttons: ['ui.cancel', {title: 'ui.delete', variant: 'red', action: 'usersRemoveAction:'+id}],
					centerMode: true,
					winClass: 'ddrpopup_dialog color-red'
				});
				
				removeUsersPopup.then(({close, wait}) => {
					$.usersRemoveAction = (btn) => {
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
		
		
		$.usersSetRules = (rowBtn, userId, pseudoname) => {
			let row = $(rowBtn).closest('tr');
			ddrPopup({
				title: 'Права доступа <span class="fz13px color-gray">'+pseudoname+'</span> ', // заголовок,
				url: 'ajax/users/permissions',
				params: {row, view: 'admin.section.personal.render.permissions', user: userId, guard: 'site'},
				width: 1000, // ширина окна
				buttons: ['ui.close']
			});
			
			$.setPermissionToUser = (btn, userId, permissionId) => {
				let checkStat = $(btn).is(':checked') ? 1 : 0;
				query({
					method: 'put',
					route: 'permissions',
					data: {user: userId, permission: permissionId, stat: checkStat}
				}, (stat, container, {error, status, headers}) => {
					if (error) {
						$.notify(error?.message, 'error');
						return false;
					}
					 
					let select = $(row).find('[name="role"]');
					
					if (!stat?.was_role && !stat?.has_permissions) {
						$(select).ddrInputs('setOptions', {
								title: 'Роль не выбрана',
								defaultSelected: true,
								selected: true,
								disabled: true
							}, 'option[disabled]', 'replace');
					} else {
						let title;
						if ((stat?.was_role && stat?.has_permissions ) || !stat?.was_role && stat?.has_permissions) {
							title = 'Кастомная роль';
						} else if (stat?.was_role && !stat?.has_permissions) {
							title = 'Роль не выбрана';
						}
						
						let optionSelector = $(select).children('option[disabled]').length ? 'option[disabled]' : 'before';
						$(select).ddrInputs('setOptions', {
							title,
							defaultSelected: true,
							selected: true,
							disabled: true
						}, optionSelector, 'replace');
					}
					
				});
			}
		}
		
		
		
		
		
			
		
		
		
		
		
		$.usersSendEmail = (btn, userId) => {
			let row = $(btn).closest('tr');
			
			let usersSendEmail = $(row).ddrWait({
				iconHeight: '26px',
				bgColor: '#ffffffd6'
			});
			
			query({
				method: 'post',
				route: 'send_email',
				data: {id: userId}
			}, (stat, container, {error, status, headers}) => {
				usersSendEmail.destroy();
				if (stat) {
					$(btn).ddrInputs('removeClass', 'button-green');
					$(btn).ddrInputs('addClass', 'button-light');
					$.notify('Письмо успешно отправлено!');
				} 
				else {
					if (status == 429) $.notify('Слишком частая отправка писем!', 'error');
					else $.notify(error?.message, 'error');
				}
			});
		}
		
		
		
		
		
		
		
		
		$.userSettings = async (btn, userId) => {
			const {
				popper,
				enableButtons,
			} = await ddrPopup({
				url: 'ajax/users/settings',
				method: 'get',
				params: {view: viewsPath, id: userId},
				title: 'Настройки пользователя', // заголовок
				width: 1000, // ширина окна
				buttons: ['ui.close'/*, {title: 'Сохранить', action: 'userSettingsSaveBtn', disabled: 1}*/]
			});
			
			
			$(popper).ddrInputs('change:one', function() {
				enableButtons(true);
			});
			
			
			$(popper).find('[name]').ddrBuildInputsData({
				//onBefore(inp, e) {},
				async onChange({setting, value, type, remove, inp, done}) {
					//console.log('onChange');
					//let abortContr = createAbortCtrl();
					
					const {data, error, status, headers, abort} = await ddrQuery.put('ajax/users/settings', {id: userId, setting, value, type, remove}/*, {abortContr}*/);
					
					done();
					
					if (error) {
						console.log(error);
						$.notify(error?.message, 'error');
						return;
					}
				},
			});
		}
		
		
		
		
		
		
	});
	
	
</script>