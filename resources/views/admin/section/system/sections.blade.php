<section>
	<x-settings>
		<x-card
			loading="{{__('ui.loading')}}"
			ready
			class="p0"
			>
			<div class="ddrtabs">
				<div class="ddrtabs__nav">
					<ul class="ddrtabsnav" ddrtabsnav>
						<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="sectionsTab1">Заказы</li>
						<li class="ddrtabsnav__item" ddrtabsitem="sectionsTab2">События</li>
						<li class="ddrtabsnav__item" ddrtabsitem="sectionsTab3">Подтверждение готовности заказов</li>
					</ul>
				</div>
				
				{{--  --}}
				<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
					<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="sectionsTab1">
						<div class="row row-cols-1 gy-20">
							<div class="col">
								<p class="mb1rem">Количество записей на одной странице списка заказов</p>
								<x-input
									type="number"
									class="w7rem"
									group="large"
									label="строк"
									min="4"
									max="50"
									showrows
									setting="orders.per_page"
								/>
							</div>
							<div class="col">
								<p class="mb1rem">Присвоить типы заказов</p>
								<x-button size="normal" variant="light" setorderstypes action="setOrdersTypes:empty">Заказам без типов</x-button>
								<x-button size="normal" variant="light" setorderstypes action="setOrdersTypes">Всем заказам</x-button>
							</div>
						</div>
					</div>
					
					<div class="ddrtabscontent__item" ddrtabscontentitem="sectionsTab2">
						<div class="row row-cols-1 gy-20">
							<div class="col">
								<p class="mb1rem">Отправка в прошедшие спустя часов:</p>
								<x-input
									type="number"
									class="w7rem"
									group="large"
									label="часов"
									min="0"
									max="50"
									showrows
									setting="timesheet.to_past_hours"
								/>
							</div>
							
							<div class="col">
								<p class="mb1rem">Поиск событий для привязки заказа со смещением часов:</p>
								<x-input
									type="number"
									class="w7rem"
									group="large"
									label="часов"
									min="-50"
									max="50"
									showrows
									setting="timesheet.attach_order_events_offset_hours"
								/>
							</div>
						</div>
					</div>
					
					
					<div class="ddrtabscontent__item" ddrtabscontentitem="sectionsTab3">
						<div class="row row-cols-1 gy-20">
							<div class="col">
								<p class="mb1rem">Вебхук</p>
								<x-textarea
									type="number"
									class="w50rem"
									group="large"
									label="строк"
									setting="confirm_orders.webhook"
								/>
							</div>
							<div class="col">
								<p class="mb1rem">Сообщение</p>
								<x-textarea
									type="number"
									class="w50rem"
									group="large"
									label="строк"
									setting="confirm_orders.message"
								/>
							</div>
							<div class="col">
								<code class="d-block mb2rem">
									<strong>Подстановка данных</strong>

									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{raw}}')" title="Скопировать">@{{raw}} 				- Тело заказа</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{timezone}}')" title="Скопировать">@{{timezone}}		- Временная зона</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{status}}')" title="Скопировать">@{{status}}			- Статус заказа</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{order}}')" title="Скопировать">@{{order}}				- Номер заказа</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{order_type}}')" title="Скопировать">@{{order_type}}	- Тип заказа</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{price}}')" title="Скопировать">@{{price}} 			- Стаомость</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{server_name}}')" title="Скопировать">@{{server_name}}	- Инвайт</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{link}}')" title="Скопировать">@{{link}} 				- Ссылка</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{date_add}}')" title="Скопировать">@{{date_add}} 		- Дата добавления</p>
									<p class="color-gray-500 color-gray-600-hovered pointer color-green-active" onclick="$.copyToClipboard(event, '@{{date_msc}}')" title="Скопировать">@{{date_msc}} 		- Дата по МСК</p>
								</code>
							</div>
							
						</div>
					</div>
				</div>
				{{--  --}}
				
			</div>
		</x-card>
	</x-settings>
</section>






<script type="module">
	$.setOrdersTypes = async (btn, type = null) => {
		
		$('[setorderstypes]').not(btn).ddrInputs('disable');
		
		const empty = type == 'empty' ? 1 : 0
		const btnWait = $(btn).ddrWait({
			iconHeight: '20px',
			bgColor: '#e9e9e9d4'
		});
		
		const {data, error, status, headers} = await ddrQuery.get('orders_set_types', {empty});
		
		btnWait.destroy();
		$('[setorderstypes]').not(btn).ddrInputs('enable');
		
		if (error) {
			console.log(error);
			$.notify(error?.message, 'error');
			return;
		}
		
		$.notify('Присвоение прошло успешно!');		
	}
</script>