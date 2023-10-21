<x-table class="w100" id="timesheetTable" noborder scrolled="{{$scrolled ?? '68vh'}}">
	<x-table.head>
		<x-table.tr noborder>
			<x-table.td class="w20rem" noborder><strong>Дата (МСК)</strong></x-table.td>
			<x-table.td class="w20rem" noborder><strong>Время (МСК / ОРИГ)</strong></x-table.td>
			<x-table.td class="w-auto" noborder>
				<div class="row g-5 align-items-center">
					<div class="col-auto"><strong title="Название команды">Команда</strong></div>
					<div class="col"><div tscommandshooser></div></div>
				</div>
			</x-table.td>
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-show-price:site')))
				<x-table.td class="w8rem" noborder><strong>Сумма</strong></x-table.td>
			@endif
			<x-table.td class="w30rem" noborder><strong>Комментарий</strong></x-table.td>
			<x-table.td class="w30rem" noborder><strong>Тип события</strong></x-table.td>
			<x-table.td class="w7rem" noborder><strong>Кол-во заказов</strong></x-table.td>
			<x-table.td class="w12rem" noborder><strong>Действия</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body id="timesheetList" class="minh-4rem" emptytext="Нет записей"></x-table.body>
</x-table>