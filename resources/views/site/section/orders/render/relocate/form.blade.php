<x-input-group size="small">
	
	@isset($rawData)
		<p class="mb2rem color-gray-600 select-text code">{{$rawData}}</p>
	@endisset
	
	<div class="row row-cols-1 gy-20 mb2rem">
		<div class="col">
			<p class="color-gray-500 fz14px mb3px">Выбрать дату:</p>
			<input type="hidden" id="relocateOrderCalendar" />
		</div>
		
		<div class="col">
			<div class="row justify-content-between">
				<div class="col-auto">
					<x-chooser
						variant="neutral"
						px="10"
						id="toTSRegionsChuser"
						class="mb1rem"
						>
						@foreach($regions as $rId => $rTitle)
							<x-chooser.item
								action="toTimesheetChooseRegion:{{$rId}}"
								regionid="{{$rId}}"
								active="{{$regionId ? $rId == $regionId : $loop->first}}"
								>{{$rTitle}}</x-chooser.item>
						@endforeach
					</x-chooser>
				</div>
				<div class="col-auto">
					<x-chooser
						variant="neutral"
						px="10"
						id="toTimesheetActualPast"
						class="mb1rem"
						>
						<x-chooser.item
							action="toTimesheetChooseActualPast:actual"
							period="actual"
							active
							title="Актуальные"
							>Акт.</x-chooser.item>
						<x-chooser.item
							action="toTimesheetChooseActualPast:past"
							period="past"
							title="Прошедшие"
							>Прош.</x-chooser.item>
					</x-chooser>
				</div>
			</div>
			
			
			<p class="color-gray-500 fz14px mb3px">Выбрать событие:</p>
			<x-table class="w100" scrolled="calc(100vh - 600px)" noborder>
				<x-table.head>
					<x-table.tr>
						<x-table.td class="w13rem" noborder><strong class="fz12px">Дата (МСК)</strong></x-table.td>
						<x-table.td class="w6rem" noborder><strong class="fz12px">Время (МСК)</strong></x-table.td>
						<x-table.td class="w6rem" noborder><strong class="fz12px">Время клиента</strong></x-table.td>
						<x-table.td class="w-auto" noborder><strong class="fz12px">Название команды</strong></x-table.td>
						<x-table.td class="w22rem" noborder><strong class="fz12px">Тип события</strong></x-table.td>
						<x-table.td class="w6rem" noborder><strong class="fz12px">Кол-во заказов</strong></x-table.td>
					</x-table.tr>
				</x-table.head>
				<x-table.body class="minh-10rem" emptytext="Нет событий"></x-table.body>
			</x-table>
		</div>
		
		<div class="col">
			<p class="color-gray-500 fz14px mb3px">Комментарий:</p>
			<x-textarea name="comment" class="w100" rows="3" placeholder="Введите текст" noresize />
		</div>
	</div>
		
	<input type="hidden" name="timesheet_id" value=""> 
	
	{{-- @if($type == 'move')
		<input type="hidden" name="timezone_id" value="{{$timezone['id'] ?? null}}">
		<input type="hidden" name="date" value="{{$date ?? null}}">
	@endif --}}
	
</x-input-group>