<x-table class="w100" id="timesheetTable" noborder scrolled="{{$scrolled ?? '68vh'}}">
	<x-table.head>
		<x-table.tr class="h5rem" noborder>
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-date:site')))
				<x-table.td class="w15rem" noborder><strong>Дата (МСК)</strong></x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-time:site')))
				<x-table.td class="w16rem" noborder><strong>Время (МСК / ОРИГ)</strong></x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-command:site')))
				<x-table.td class="w-auto" noborder>
					<div class="row gx-5 align-items-center">
						<div class="col-auto"><strong title="Название команды">Команда</strong></div>
						<div class="col"><div tscommandschooser></div></div>
					</div>
				</x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-stat:site')))
				<x-table.td class="w26rem" noborder>
					<strong title="Количество заказов по типам">Статистика</strong>
				</x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-show-price:site')))
				<x-table.td class="w8rem" noborder><strong>Сумма</strong></x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-type:site')))
				<x-table.td class="w26rem" noborder>
					<div class="row g-5 align-items-center">
						<div class="col-auto"><strong title="Тип события">Тип события</strong></div>
						<div class="col"><div eventstypesсhooser></div></div>
					</div>
				</x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-comment:site')))
				<x-table.td class="w20rem" noborder><strong>Комментарий</strong></x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('screenstat:site')))
				<x-table.td class="w4rem h-center" noborder title="Отправка скриншотов и статистики">
					<i class="fa-solid fa-fw fa-receipt"></i>
				</x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-orders-count:site')))
				<x-table.td class="w7rem" noborder><strong>Кол-во заказов</strong></x-table.td>
			@endif
			
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-actions:site')))
				<x-table.td class="w13rem" noborder><strong>Действия</strong></x-table.td>
			@endif
		</x-table.tr>
	</x-table.head>
	<x-table.body id="timesheetList" class="minh-4rem" emptytext="Нет записей"></x-table.body>
</x-table>