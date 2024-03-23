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
			<div id="lastTimesheetPeriodsBlock" class="ml3rem minw-4rem maxw-35rem h6rem pr10px"></div>
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
	
	
	
	<div class="row justify-content-between minh3rem-8px">
		<div class="col-auto">
			<x-chooser variant="neutral" size="normal" px="20" py="5" class="mb1rem" id="regionChooser" hidden>
				@forelse($setting['regions'] as $regionId => $regionTitle)
					<x-chooser.item
						action="setRegionAction:{{$regionId}}"
						active="{{$loop->first}}"
						regionchooser="{{$regionId}}"
						>{{$regionTitle ?? '-'}}</x-chooser.item>
				@empty
					<p>-</p>
				@endforelse
			</x-chooser>
		</div>
		<div class="col-auto">
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
		</div>
	</div>
	
	
	<div id="timesheetContainer" class="timesheetcontainer pt2rem"><p class="color-gray-400 fz16px noselect text-center">Выберите период</p></div>
	
</section>








<script type="module">
	
	const sectionName = location.pathname.replace('/', '');
	const listType = ref(ddrStore('listType') || 'actual');
	const regionId = ref(ddrStore('eventsRegion') || Number($('#regionChooser').find('[regionchooser]').attr('regionchooser')));
	const timesheetCrudList = ref(null);
	const choosedPeriod = ref(ddrStore(`${sectionName}-choosedPeriod`));
	
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
	
	//const viewsPath = 'admin.section.system.render.timesheet';
	
	
	
	
	const lastTimesheetPeriodsWaitBlock = $('#lastTimesheetPeriodsBlock').ddrWait({
		iconHeight: '26px',
	});
	
	
	
	$('#openTimesheetPeriodsBtn').ddrInputs('enable');
	$.openTimesheetPeriodsWin = async () => {
		timesheetPeriodsCrud(getLastTimesheetPeriods, timesheetCrud, listType, regionId, timesheetCrudList, choosedPeriod, lastTimesheetPeriodsWaitBlock);
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
		ddrStore(`${sectionName}-choosedPeriod`, periodId);
		
		timesheetCrud(periodId, listType, regionId, buildOrdersTable, (list) => {
			timesheetCrudList.value = list;
			isBuildesPeriod = false;
			periodsBlockWait.destroy();
		});
		$(btn).addClass('active');
	}
	
	
	
	
	$.timesheetGetOrders = (row, timesheetId) => {
		if (event && $(event.target).closest('[timesheetrulesblock]').length) return false;
		timesheetOrders(row, timesheetId);
	}
	
	
	
	$.setListTypeAction = (btn, isActive, type) => {
		if (isActive) return;
		setListTypeAction('listtype', type)
	}
	
	$.setRegionAction = (btn, isActive, type) => {
		if (isActive) return;
		setListTypeAction('region', type)
	}
	
	
	function setListTypeAction(type = 'listtype', val = null) {
		if (!val) return false;
		
		const timesheetContainerWait = $('#timesheetContainer').ddrWait({
			iconHeight: '50px',
			text: 'Загрузка записей...'
		});
		
		if (type == 'listtype') {
			listType.value = val;
			ddrStore('listType', val);
		} else if (type == 'region') {
			regionId.value = val;
			ddrStore('eventsRegion', val);
		}
		
		timesheetCrudList.value({
			list_type: listType.value,
			region_id: regionId.value,
			command_id: _.get(ddrStore('timesheet-filter'), regionId.value+'.command', null),
			event_type: _.get(ddrStore('timesheet-filter'), regionId.value+'.eventtype', null),
		}, () => {
			$('#timesheetTable').blockTable('buildTable');
			timesheetContainerWait.destroy();
		});
	}
	
	
	
	$.openLink = (btn, url) => {
		if (!url) return;
		window.open(url, '_blank');
	}
	
	
	$.openRawDataHistoryWin = (btn, orderId, orderName) => {
		rawDataHistory(orderId, orderName, btn);
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
			width: 700, // ширина окна
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
		
		
		
		$(popper).on(tapEvent, '[openttscommentimg]', async function() {
			const src = $(this).attr('openttscommentimg');
			
			
			$('body').append(`<div class="ddrpopup ddrpopup_opening" id="tsCommentsImg"><div class="ddrpopup__wrap" ddrpopupwrap="">\
				<div class="ddrpopup__container">\
					<div class="ddrpopup__win noselect ddrpopup__win_opening w100" ddrpopupwin="">\
						<img src="${src}" class="w100 h-auto" />\
					</div>\
				</div>\
			</div>`);
			
			
			$('#tsCommentsImg').one(tapEvent, function() {
				$(this).remove();
			});
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
	
	
	
	
	$.copyInviteColumn = (btn, fraction = null) => {
		const cell = $(btn).closest('[ddrtabletd]'),
			rowIndex = $(cell).index(),
			data = [];
		
		let rows;
		
		if (fraction) {
			rows = $(cell).closest('[ddrtablehead]').siblings('[ddrtablebody]').find(`[ddrtabletr][fraction="${fraction}"]`);
		} else {
			rows = $(cell).closest('[ddrtablehead]').siblings('[ddrtablebody]').find(`[ddrtabletr][fraction]`);
		}
		
		if (!rows) {
			$.notify('Нечего копировать', 'info');
			return;
		}
		
		$(rows).each(function(k, row) {
			let cell = $(row).find(`[ddrtabletd]:eq(${rowIndex})`);
			let cellValue = $(cell).text().trim();
			if (cellValue) data.push(cellValue);
		});
		
		const resultStr = data.join("\n");
		
		if (resultStr) $.copyToClipboard(event, resultStr);
	}
	
	
	$.copyOrdersColumn = (elem) => {
		const cell = $(elem).closest('[ddrtabletd]'),
			rowIndex = $(cell).index(),
			data = [];
			
		$(cell).closest('[ddrtablehead]').siblings('[ddrtablebody]').find('[ddrtabletr]').each(function(k, row) {
			let cell = $(row).find(`[ddrtabletd]:eq(${rowIndex})`);
			let cellValue = $(cell).text().trim();
			if (cellValue && cellValue != '-') data.push(cellValue);
		});
		
		if (!data.length) {
			$.notify('Нечего копировать', 'info');
			return;
		}
		
		const resultStr = data.join("\n");
		
		if (resultStr) $.copyToClipboard(event, resultStr);
	}
	
	
	
	
	$.copyBattleTagColumn = (elem) => {
		const cell = $(elem).closest('[ddrtabletd]'),
			rowIndex = $(cell).index(),
			data = [];
			
		$(cell).closest('[ddrtablehead]').siblings('[ddrtablebody]').find('[ddrtabletr]').each(function(k, row) {
			let cell = $(row).find(`[ddrtabletd]:eq(${rowIndex})`);
			let cellValue = $(cell).text().trim();
			if (cellValue && cellValue != '-') data.push(cellValue);
		});
		
		if (!data.length) {
			$.notify('Нечего копировать', 'info');
			return;
		}
		
		const resultStr = data.join("\n");
		
		if (resultStr) $.copyToClipboard(event, resultStr);
	}
	
	
	
	
	
	
	
	
	$.slackNotifyAction = async (btn, id, orderId, timesheet_id) => {
		const btnWait = $(btn).ddrWait({
			iconHeight: '15px',
			bgColor: '#f5f5f5b5',
		});
		
		const {data, error, status, headers} = await ddrQuery.post('slack/send_message', {id, order_id: orderId, timesheet_id});
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		if (!data) {
			$.notify('Невозможно отправить уведомление!', 'info');
			btnWait.destroy();
			return;
		}
		
		
		if (status == 200) {
			$.notify('Уведомление успешно отправлено!');
			setTimeout(() => {
				btnWait.destroy();
			}, (data?.timeout || 0) * 1000);
		}
	}
	
	
	
	
	
	
	
	
	//-------------------------------------------------------------------------- Скриншоты и статистика 
	$.timesheetScreenStat = async (btn, timesheetId) => {
		event.stopPropagation();
		
		const {
			popper,
			wait,
			close,
			enableButtons,
		} = await ddrPopup({
			url: 'crud/timesheet/screenstat',
			method: 'get',
			params: {timesheet_id: timesheetId, views: 'admin.section.system.render.timesheet'},
			title: 'Скриншты и статистика', // заголовок
			width: 830, // ширина окна
			buttons: ['ui.close', {action: 'screenStatSend', title: 'Отправить'}],
			disabledButtons: true, // при старте все кнопки кроме закрытия будут disabled
		});
		
		enableButtons('close');
		
		if ($('#tabScreenstatHistory').hasAttr('onlyhistory')) await getScreenstatHistory();
		
		
		let ssEventTypeId, screenshot, comment;
		
		$.chooseScreenStatEvebtType = (btn, isActive, etId) => {
			if (isActive) return;
			
			if (!ssEventTypeId) enableButtons(true);
				
			ssEventTypeId = etId;
		};
		
		
		$.setScreenshot = (input) => {
			screenshot = $(input).val();
		}
		
		$.setComment = (input) => {
			comment = $(input).val();
		}
		
		
		$.ssChooseOrderNumber = (check) => {
			const checkBlock = $(check).closest(`#ssOrdersNumbers`),
				countChoosedOrders = $(checkBlock).find('[ordertypeorder]:checked').length;
			$(check).closest(`#checklabelwrapper`).find('[ssorderscout]').val(countChoosedOrders);
		}
		
		
		$.screenStatSend = async () => {
			wait();
			const stat = {};
			$('#ssStatOrdersForm').find('[ordertypeid]:checked').each((k, item) => {
				const orderTypeId = Number($(item).attr('ordertypeid')),
					ordersCount = $(`#checklabel${orderTypeId}block`).find('[ssorderscout]')?.val();
				stat[orderTypeId] = {count: Number(ordersCount), items: []}
				
				$(`#checklabel${orderTypeId}block`).find('[ordertypeorder]:checked').each((k, item) => {
					const orderNumber = $(item).attr('ordertypeorder');
					stat[orderTypeId]['items'].push(orderNumber);
				});
				
				
			});
			
			
			const {data, error, status, headers} = await ddrQuery.post('crud/timesheet/screenstat', {
				timesheet_id: timesheetId,
				stat,
				screenshot,
				comment,
				eventtype_id: ssEventTypeId,
				user_type: 'admin'
			});
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				wait(false);
				return;
			}
			
			if (data?.created && !data?.send) {
				$.notify('Статистика успешно сохранена но не отправлена!', 'info');
			} else if (data?.created && data?.send) {
				$.notify('Статистика успешно сохранена и отправлена!');
			}
			
			close();
		}
		
		
		
		
		// Получить историю отправки статистики
		$.getScreenstatHistory = async (btn, isActive) => {
			if (isActive) return;
			getScreenstatHistory();
		}
		
		
		async function getScreenstatHistory() {
			$('#creenStatHistory').html('');
			
			const waitHistory = $('#creenStatHistory').ddrWait({
				iconHeight: '35px',
				bgColor: '#fdfdfdb5',
			});
			
			
			const {data, error, status, headers} = await ddrQuery.get('crud/timesheet/screenstat_history', {timesheet_id: timesheetId, views: 'admin.section.system.render.timesheet'});
			
			waitHistory.destroy();
			
			if (error) {
				console.log(error);
				$.notify(error?.message, 'error');
				wait(false);
				return;
			}
			
			$('#creenStatHistory').html(data);
		}
		
		
		
		$.openOrigScreenshot = (href) => {
			let imgHtml = '';
			imgHtml += '<div class="ddrpopup ddrpopup_opening" onclick="this.remove()">';
			imgHtml += 	'<div class="ddrpopup__wrap" ddrpopupwrap="">';
			imgHtml += 		'<div class="ddrpopup__container">';
			imgHtml += 			'<div class="ddrpopup__win noselect ddrpopup__win_opening w100" ddrpopupwin="">';
			imgHtml += 				`<img src="${href}" class="w100 h-auto">`;
			imgHtml += 			'</div>';
			imgHtml += 		'</div>';
			imgHtml += 	'</div>';
			imgHtml += '</div>';
			
			$('body').append(imgHtml);
		}
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
	
	
	// Автовыбор предыдущего выбранного периода
	if (choosedPeriod.value) {
		timesheetCrud(choosedPeriod.value, listType, regionId, buildOrdersTable, (list) => {
			timesheetCrudList.value = list;
			$('#listTypeChooser').find(`[listtypechooser]`).removeClass('chooser__item_active');
			$('#listTypeChooser').find(`[listtypechooser="${listType.value}"]`).addClass('chooser__item_active');
			
			$('#regionChooser').find(`[regionchooser]`).removeClass('chooser__item_active');
			$('#regionChooser').find(`[regionchooser="${regionId.value}"]`).addClass('chooser__item_active');
		});
	}
	
	
	
	
	
</script>