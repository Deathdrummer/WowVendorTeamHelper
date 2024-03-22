<section>
	<x-settings>
		<x-card
			loading="{{__('ui.loading')}}"
			ready
			>
			<div class="ddrtabs">
				<div class="ddrtabs__nav">
					<ul class="ddrtabsnav" ddrtabsnav>
						<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="systemTab1">Общие настройки</li>
						<li class="ddrtabsnav__item" ddrtabsitem="systemTab2">Заголовки и названия</li>
						<li class="ddrtabsnav__item" ddrtabsitem="systemTab3">Настройки отображения элементов</li>
						<li class="ddrtabsnav__item" ddrtabsitem="systemTab4">Тестирование парсера заказов</li>
					</ul>
				</div>
				
				{{--  --}}
				<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
					<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="systemTab1">
						<div class="row row-cols-1 gy-20">
							<div class="col">
								<x-input
									class="w30rem"
									label="Стартовая страница клиентской части"
									group="large"
									setting="site_start_page"
									/>
							</div>
							
							<div class="col">
								<x-input
									class="w30rem"
									label="Стартовая страница админ. панели"
									group="large"
									setting="admin_start_page"
									/>
							</div>
							
							<div class="col">
								<x-checkbox
									label="Показывать главное меню в клиентской части"
									group="large"
									setting="show_nav"
									/>
							</div>
							
							<div class="col">
								<x-checkbox
									label="Показывать выбор языка"
									group="large"
									setting="show_locale"
									/>
							</div>
						</div>
					</div>
					
					<div class="ddrtabscontent__item" ddrtabscontentitem="systemTab2">
						<div class="row">
							<div class="col-auto">
								<x-input
									label="Название компании"
									group="large"
									setting="company_name"
									/>
							</div>
						</div>
					</div>
					
					
					<div class="ddrtabscontent__item" ddrtabscontentitem="systemTab3">
						
						<div class="row row-cols-3 gx-15">
							<div class="col">
								<p class="mb2rem fz16px">Варинты отображения статусов заказа в тултипе:</p>
								<div class="row row-cols-1 gy-10">
									<div class="col">
										<x-radio
											label="Цветной круг"
											setting="order_statuses_showtype"
											group="large"
											value="color"
											/>
									</div>
									<div class="col">
										<x-radio
											label="Цветная иконка"
											setting="order_statuses_showtype"
											group="large"
											value="icon"
											/>
									</div>
								</div>
							</div>
							<div class="col">
								<p class="mb2rem fz16px">Варинты отображения статусов заказа в списке заказов:</p>
								<div class="row row-cols-1 gy-10">
									<div class="col">
										<x-checkbox
											label="Цветной круг"
											setting="order_statuses_showtype_list.color"
											group="large"
											value="color"
											/>
									</div>
									<div class="col">
										<x-checkbox
											label="Цветная иконка"
											setting="order_statuses_showtype_list.icon"
											group="large"
											value="icon"
											/>
									</div>
									<div class="col">
										<x-checkbox
											label="Текст"
											setting="order_statuses_showtype_list.text"
											group="large"
											value="text"
											/>
									</div>
								</div>
							</div>
							<div class="col">
								
							</div>
						</div>	
					</div>
					
					<div class="ddrtabscontent__item" ddrtabscontentitem="systemTab4">
						<x-textarea
							class="w50"
							size="normal"
							placeholder="Вставить тело заказа"
							oninput="$.parseOrderData(event, this)"
						></x-textarea>
						
						<div class="mt4rem minh26rem" id="parseResContainer">
							<ul id="ordersParserResult" class="ddrlist format fz12px"></ul>
						</div>
					</div>
					
					
					
					
					{{-- <x-button size="normal" variant="blue" class="mt2rem" ddrrool-element="test">MENU</x-button>
					
					<div ddrrool-target="test" ddrrool-class="bg-green"><span class="p10px d-block">sfsd gsd gsdgsd gsdgs dgsdg </span></div> --}}
					
				</div>
				{{--  --}}
			</div>
		</x-card>
	</x-settings>
</section>






<script type="module">
	
	let parserTOut;
	$.parseOrderData = (e, textarea) => {
		clearTimeout(parserTOut);
		parserTOut = setTimeout(async () => {
			const ddrWait = $('#parseResContainer').ddrWait({iconHeight: '40px', backgroundColor: '#fffa', iconColor: 'hue-rotate(170deg)'});
			
			const value = e.target.value;
			const {data, error, status, headers} = await ddrQuery.post('admin/test_parser', {order_data: value});
			
			if (data?.error || !data.length) {
				$('#ordersParserResult').text('Не удалось спарсить данные...');
				ddrWait.destroy();
				return false;
			}
			
			$('#ordersParserResult').empty();
			
			for (let row of data) {
				for (const [field, value] of Object.entries(row)) {
					$('#ordersParserResult').append(`<li class="code ddrlist__item mt1rem"><div class="row"><div class="col-auto"><strong>${field}:</strong></div><div class="col"><p class="fz12px color-gray-600">${value || '-'}</p></div></div></li>`);
				}
				
				$('#ordersParserResult').append('<hr class="hr hr-light mt2rem mb2rem">');
			}
			
			ddrWait.destroy();
		}, 500);	
	}
	
	
	
	
	$.openPopupWin = () => {
		ddrPopup({
			
			title: 'Тестовый заголовок',
			width: 400, // ширина окна
			html: '<p>Контентная часть</p>', // контент
			buttons: ['ui.close', {action: 'tesTest', title: 'Просто кнопка'}],
			buttonsAlign: 'center', // выравнивание вправо
			//disabledButtons, // при старте все кнопки кроме закрытия будут disabled
			//closeByBackdrop, // Закрывать окно только по кнопкам [ddrpopupclose]
			//changeWidthAnimationDuration, // ms
			//buttonsGroup, // группа для кнопок
			//winClass, // добавить класс к модальному окну
			//centerMode, // контент по центру
			//topClose // верхняя кнопка закрыть
		}).then(({state, wait, setTitle, setButtons, loadData, setHtml, setLHtml, dialog, close, onScroll, disableButtons, enableButtons, setWidth}) => { //isClosed
						
		});
	}
	
	
	//$('button').ddrInputs('disable');
	
	
	/*$('#testRool').ddrInputs('error', 'error');
	$('#testSelect').ddrInputs('error', 'error');
	$('#testCheckbox').ddrInputs('error', 'error');
	
	
	$('#openPopup').on(tapEvent, function() {
		ddrPopup({
			title: 'auth.greetengs',
			lhtml: 'auth.agreement'
		}).then(({wait}) => {
			//wait();
		});
	});*/
</script>

