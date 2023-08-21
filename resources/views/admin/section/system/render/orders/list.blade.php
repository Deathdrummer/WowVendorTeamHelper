@if(count($list))
	<x-table class="w100" scrolled="300px">
		<x-table.head>
			<x-table.tr class="h3rem">
				<x-table.td class="w4rem h-center"><strong>№</strong></x-table.td>
				@cando('nomer-zakaza-(klient):site')<x-table.td class="w8rem-4px"><strong>№ заказа</strong></x-table.td> @endcando
				@cando('data-(klient):site')<x-table.td class="w19rem"><strong>Дата</strong></x-table.td> @endcando
				@cando('dannye-(klient):site')<x-table.td class="w-auto"><strong>Данные</strong></x-table.td> @endcando
				@cando('invayt-(klient):site')<x-table.td class="w16rem"><strong>Инвайт</strong></x-table.td> @endcando
				@cando('kommentariy-(klient):site')<x-table.td class="w-30rem"><strong>Комментарий</strong></x-table.td> @endcando
				@cando('stoimost-(klient):site')<x-table.td class="w-9rem"><strong>Стоимость</strong></x-table.td> @endcando
				
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
								<i class="fa-regular fa-bell" title="Уведомления"></i>
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
					<strong>{{$showType['text'] ? 'Статус' : 'Стат'}}</strong>
				</x-table.td>
				@endcando
				
				@cando('ssylka-(klient):site')<x-table.td class="w4rem h-center" title="Ссылка"><i class="fa-solid fa-fw fa-link"></i></x-table.td> @endcando
				
				@cando('deystviya-(klient):site')<x-table.td class="w-10rem"><strong>Действия</strong></x-table.td> @endcando
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