<section>
	<x-settings>
		
		
		<div class="ddrtabs">
			<div class="ddrtabs__nav">
				<ul class="ddrtabsnav" ddrtabsnav>
					<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="sectionsTab1">Простые списки</li>
					<li class="ddrtabsnav__item" ddrtabsitem="sectionsTab2">Составные списки</li>
					<li class="ddrtabsnav__item" ddrtabsitem="sectionsTab3">Статусы заказов</li>
				</ul>
			</div>
			
			{{--  --}}
			<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
				<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="sectionsTab1">
					
					<div class="row g-10">
						<div class="col-4">
							<x-card
								loading
								ready
								title="Временные зоны"
								desc="Временные зоны со смещением времени относительно Московского времени"
								>
								<x-simplelist
									setting="timezones"
									fieldset="ID:w4rem|number|id|1,Временная зона:w10rem|text|timezone,Смещение от МСК:w8rem|number|shift,Формат 24 ч.:w7rem|checkbox|format_24"
									{{-- options="name_type;foo:fooval,bar:barval|name_radio;foo:rool,bar:tool" --}}
									{{-- onRemove="removeCustomerAction" --}}
									group="small"
								 />
							</x-card>
						</div>
						
						<div class="col-4">
							<x-card
								loading
								ready
								title="Регионы"
								desc=""
								>
								<x-simplelist
									setting="regions"
									fieldset="ID:w4rem|number|id|1,Название|text|title"
									{{-- options="name_type;foo:fooval,bar:barval|name_radio;foo:rool,bar:tool" --}}
									{{-- onRemove="removeCustomerAction" --}}
									group="small"
								 />
							</x-card>
						</div>
						
						<div class="col-4">
							<x-card
								loading
								ready
								title="Сложности"
								desc=""
								>
								<x-simplelist
									setting="difficulties"
									fieldset="ID:w4rem|number|id|1,Название|text|title"
									{{-- options="name_type;foo:fooval,bar:barval|name_radio;foo:rool,bar:tool" --}}
									{{-- onRemove="removeCustomerAction" --}}
									group="small"
								 />
							</x-card>
						</div>
					</div>
				</div>
				
				
				<div class="ddrtabscontent__item" ddrtabscontentitem="sectionsTab2">
					<div class="row g-10">
						<div class="col-6">
							<x-card
								loading
								class="p10px"
								id="staticsCard"
								title="Команды (статики)"
								desc="Список команд (статиков)"
								button="Добавить команду"
								buttonId="commandsAddBtn"
								buttonSize="small"
								action="commandsAddBtnAction"
								buttonVariant="light"
								>
								<x-table class="w100" id="staticsTable" noborder>
									<x-table.head>
										<x-table.tr noborder>
											<x-table.td class="w-auto" noborder><strong>Название</strong></x-table.td>
											<x-table.td class="w10rem" noborder><strong>Регион</strong></x-table.td>
											<x-table.td class="w9rem" noborder><strong>Действия</strong></x-table.td>
										</x-table.tr>
									</x-table.head>
									<x-table.body id="staticsList" class="minh-4rem" emptytext="Нет команд"></x-table.body>
								</x-table>
							</x-card>
						</div>
						
						
						<div class="col-6">
							<x-card
								loading
								class="p10px"
								id="eventsTypesCard"
								title="Типы событий"
								desc="Список типов событий"
								button="Добавить тип события"
								buttonId="eventsTypesAddBtn"
								buttonSize="small"
								action="eventsTypesAddBtnAction"
								buttonVariant="light"
								>
								<x-table class="w100" id="eventsTypesTable" noborder>
									<x-table.head>
										<x-table.tr noborder>
											<x-table.td class="w-auto" noborder><strong>Название</strong></x-table.td>
											<x-table.td class="w12rem" noborder><strong>Сложность</strong></x-table.td>
											<x-table.td class="w9rem" noborder><strong>Действия</strong></x-table.td>
										</x-table.tr>
									</x-table.head>
									<x-table.body id="eventsTypesList" class="minh-4rem" emptytext="Нет типов событий"></x-table.body>
								</x-table>
							</x-card>
						</div>
					</div>
				</div>
				
				<div class="ddrtabscontent__item" ddrtabscontentitem="sectionsTab3">
					<x-table noborder class="w100">
						<x-table.head>
							<x-table.tr>
								<x-table.td class="w7rem"><strong>Статус</strong></x-table.td>
								<x-table.td class="w20rem"><strong>Название статуса</strong></x-table.td>
								<x-table.td class="w10rem"><strong>Цвет</strong></x-table.td>
								<x-table.td class="w20rem"><strong>Иконка</strong></x-table.td>
								<x-table.td class="w10rem"><strong>Отображать</strong></x-table.td>
								<x-table.td class="w7rem"><strong>Сорт</strong></x-table.td>
							</x-table.tr>
						</x-table.head>
						<x-table.body>
							@foreach(App\Enums\OrderStatus::asArray() as $statusName => $val)
								<x-table.tr class="h5rem" noborder>
									<x-table.td>
										<p>{{$statusName}}</p>
									</x-table.td>
									<x-table.td>
										<x-input
											group="normal"
											setting="order_statuses.{{$statusName}}.name"
											/>
									</x-table.td>
									<x-table.td>
										<x-input
											type="color"
											group="normal"
											setting="order_statuses.{{$statusName}}.color"
											/>
									</x-table.td>
									<x-table.td>
										<x-input
											group="normal"
											setting="order_statuses.{{$statusName}}.icon"
											/>
									</x-table.td>
									<x-table.td class="h-center">
										<x-checkbox
											group="normal"
											setting="order_statuses.{{$statusName}}.show"
											/>
									</x-table.td>
									<x-table.td>
										<x-input
											type="number"
											group="normal"
											setting="order_statuses.{{$statusName}}.sort"
											/>
									</x-table.td>
								</x-table.tr>
							@endforeach
						</x-table.body>
					</x-table>
				</div>
				
			</div>
			{{--  --}}
		
		
		</div>
		
		
		
		
		
		
		
		
	</x-settings>
	
		
	
	
	
	
	
	
	
	
	
	
	
	
		
	
	
	

	
	{{-- <div class="row g-10" hidden>
		<div class="col-4">
			<x-card
				loading
				ready
				title="Название"
				desc="писание"
				>
				<x-simplelist
					setting="simplelist"
					fieldset="Поле ввода:w20rem|input|name_title,Текстовое поле:w20rem|textarea|name_text,Выпадающий список:w20rem|select|name_type,Радио|radio|name_radio,Чекбокс|checkbox|name_checkbox"
					options="name_type;foo:fooval,bar:barval|name_radio;foo:rool,bar:tool"
					group="small"
				 />
			</x-card>
		</div>
		
		<div class="col-4">
			<x-card
				loading
				ready
				title="Название 2"
				desc="писание 2"
				>
				<x-simplelist
					setting="simplelist2"
					fieldset="Поле ввода:w20rem|input|name_title,Текстовое поле:w20rem|textarea|name_text,Выпадающий список:w20rem|select|name_type,Радио|radio|name_radio,Чекбокс|checkbox|name_checkbox"
					options="name_type;foo:fooval,bar:barval|name_radio;foo:rool,bar:tool"
					group="small"
				 />
			</x-card>
		</div>
		
		<div class="col-4">
			<x-card
				loading
				ready
				title="Название 2"
				desc="писание 2"
				>
				<x-input-group group="small">
					<div class="mb15px"><x-radio label="Радиокнапка 1" value="foo" setting="radio" /></div>
					<div class="mb15px"><x-radio label="Радиокнапка 2" value="bar" setting="radio" /></div>
					<div class="mb15px"><x-radio label="Радиокнапка 3" value="rool" setting="radio" /></div>
					<div class="mb15px"><x-radio label="Радиокнапка 4" value="well" setting="radio" /></div>
				</x-input-group>
			</x-card>
		</div>
	</div> --}}
	
	
</section>










<script type="module">
	
	
	const {staticsCrud, eventsTypesCrud} = await loadSectionScripts({section: 'lists', guard: 'admin'});

	
	staticsCrud();
	eventsTypesCrud();
	
	
	
	
	
	/*
	$.removeCustomerAction = (tr, done) => {
		let customerId = $(tr).find('[field="id"]').val();
		axiosQuery('delete', 'ajax/steps_patterns/steps', {customer: customerId}, 'json').then(({data, error, status, headers}) => {
			if (error) {
				console.log(error?.message, error?.errors);
			}
			
			done();
		}).catch((e) => {
			console.log(e);
		});
	}*/
	
	
	
	
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
