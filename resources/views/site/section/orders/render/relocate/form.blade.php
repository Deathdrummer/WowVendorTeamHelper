<x-input-group size="small">
	
	<div class="row row-cols-1 gy-20 mb2rem">
		<div class="col">
			<p class="color-gray-500 fz14px mb3px">Выбрать дату:</p>
			<input type="hidden" id="relocateOrderCalendar" />
		</div>
		
		<div class="col">
			<p class="color-gray-500 fz14px mb3px">Выбрать событие:</p>
			<x-table class="w100" scrolled="68vh" noborder>
				<x-table.head>
					<x-table.tr>
						<x-table.td class="w13rem" noborder><strong class="fz12px">Дата (МСК)</strong></x-table.td>
						<x-table.td class="w9rem" noborder><strong class="fz12px">Время (МСК)</strong></x-table.td>
						<x-table.td class="w-auto" noborder><strong class="fz12px">Название команды</strong></x-table.td>
						<x-table.td class="w23rem" noborder><strong class="fz12px">Тип события</strong></x-table.td>
						<x-table.td class="w6rem" noborder><strong class="fz12px">Кол-во заказов</strong></x-table.td>
					</x-table.tr>
				</x-table.head>
				<x-table.body class="minh-4rem" emptytext="Нет событий"></x-table.body>
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