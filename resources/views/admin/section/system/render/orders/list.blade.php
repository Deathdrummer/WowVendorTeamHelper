@if($list)
	<x-table class="w100" scrolled="300px">
		<x-table.head>
			<x-table.tr class="h3rem">
				<x-table.td class="w4rem h-center">
					<x-button
						size="verysmall"
						variant="yellow"
						tag="choosealltsdorders"
						><i class="fa-solid fa-fw fa-check-double"></i></x-button>
					</x-table.td>
				<x-table.td class="w4rem h-center"><strong>№</strong></x-table.td>
				@cando('nomer-zakaza-(klient):site')<x-table.td class="w8rem-4px pointer color-neutral-hovered" onclick="$.copyOrdersColumn(this)"><strong>№ заказа</strong></x-table.td> @endcando
				@cando('data-(klient):site')<x-table.td class="w19rem"><strong>Дата</strong></x-table.td> @endcando
				@cando('tip-zakaza-(klient):site')<x-table.td class="w14rem"><strong>Тип заказа</strong></x-table.td> @endcando
				@cando('dannye-(klient):site')<x-table.td class="w-auto"><strong>Данные</strong></x-table.td> @endcando
				@cando('invayt-(klient):site')<x-table.td class="w16rem pointer color-neutral-hovered" onclick="$.copyInviteColumn(this)"><strong>Инвайт</strong></x-table.td> @endcando
				@cando('kommentariy-(klient):site')<x-table.td class="w-30rem"><strong>Комментарий</strong></x-table.td> @endcando
				@cando('stoimost-(klient):site')<x-table.td class="w-7rem h-end" title="Стоимость"><strong>$</strong></x-table.td> @endcando
				
				@cando('dannye-(klient):site')
					<x-table.td class="w-spacer p-0"></x-table.td>
				@else
					<x-table.td class="w-auto p-0"></x-table.td>
				@endcando
				
				@cando('uvedomleniya-(klient):site')
					@if(isset($notifyButtons) && $notifyButtons)
						<x-table.td class="h-center" style="width: {{31 * count($notifyButtons ?? 1) + 10}}px;">
							@if(count($notifyButtons ?? 1) == 3)
								<strong title="Уведомления">Уведомл.</strong>
							@elseif(count($notifyButtons ?? 1) > 3)
								<strong>Уведомления</strong>
							@else
								<i class="fa-brands fa-fw fa-slack" title="Уведомления в Слак"></i>
							@endif
						</x-table.td>
					@endif	
				@endcando
				
				@cando('status-(klient):site')
					<x-table.td
						@class([
							'w-10rem' => $showType['text'] ?: false,
							'w-5rem' => ($showType['color'] || $showType['icon']) ?: false,
						])
						>
						<strong choosetslabel>{{$showType['text'] ? 'Статус' : 'Стат'}}</strong>
						<x-button
							variant="green"
							group="verysmall"
							choosetsbuttons
							hidden
							action="openStatusesTooltip:null,{{$timesheetId}}"
							title="Изменить статус выбранных заказов"
							><i class="fa-solid fa-fw fa-circle-half-stroke"></i></i>
						</x-button>	
					</x-table.td>
				@endcando
				
				@cando('ssylka-(klient):site')<x-table.td class="w4rem h-center" title="Ссылка"><i class="fa-solid fa-fw fa-link"></i></x-table.td> @endcando
				
				@cando('deystviya-(klient):site')
					<x-table.td class="w-13rem pl9px">
						<strong choosetslabel>Действия</strong>
						<x-buttons-group group="verysmall" w="2rem-5px" gx="4" inline choosetsbuttons hidden>
							<x-button variant="gray" action="detachTimesheetOrder:null,{{$timesheetId}}" title="Отвязать от события"><i class="fa-solid fa-fw fa-link-slash"></i></x-button>
							<x-button variant="yellow" action="relocateTimesheetOrder:null,{{$timesheetId}},move" title="Переместить выбранные заказы"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
							<x-button variant="yellow" action="relocateTimesheetOrder:null,{{$timesheetId}},clone" title="Клонировать выбранные заказы"><i class="fa-regular fa-fw fa-clone"></i></x-button>
						</x-buttons-group>
					</x-table.td>
				@endcando
			</x-table.tr>
		</x-table.head>
		<x-table.body>
			@foreach ($list as $item)
				@include($itemView, $item)
			@endforeach
		</x-table.body>
	</x-table>
@else
	<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>
@endif