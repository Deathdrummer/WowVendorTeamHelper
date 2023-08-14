<p class="mb6px">Выбрите тип отчета:</p>

<x-chooser
	variant="neutral"
	group="normal"
	px="5"
	py="3"
	class="mb1rem"
	>
	<x-chooser.item
		id="exportOrdersAction"
		action="exportOrdersType:all"
		class="w15rem"
		active>
		Выгрузка входящего потока по дате
	</x-chooser.item>
	<x-chooser.item
		id="exportOrdersAction"
		action="exportOrdersType:linked"
		class="w15rem"
		>
		Лист ожидания
	</x-chooser.item>
</x-chooser>



<div class="mt2rem">
	<div exportordersform="all">
		
		<div class="row">
			<div class="col-auto">
				<p class="mb3px color-gray-500">Дата от:</p>
				<x-datepicker
					id="exportOrdersDateFrom"
					calendarid="exportOrdersDate"
					size="normal"
					name="date_from"
					class="w20rem"
					date="{{isset($datetime) ? DdrDateTime::date('now', ['format' => 'YYYY-MM-DD']) : null}}"
					/>
			</div>
			<div class="col-auto">
				<p class="mb3px color-gray-500">Дата до:</p>
				<x-datepicker
					id="exportOrdersDateTo"
					calendarid="exportOrdersDate"
					size="normal"
					name="date_to"
					class="w20rem"
					date="{{isset($datetime) ? DdrDateTime::date('now', ['format' => 'YYYY-MM-DD']) : null}}"
					/>
			</div>
		</div>
				
		
		
	</div>
	
	
	<div exportordersform="linked" hidden>
		<p>linked</p>
	</div>
</div>