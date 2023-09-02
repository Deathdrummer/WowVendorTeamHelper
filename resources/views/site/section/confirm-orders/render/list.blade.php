@if(count($list))
	<x-table class="w100" scrolled="564px">
		<x-table.head>
			<x-table.tr class="h3rem">
				<x-table.td class="w4rem h-center"><strong>№</strong></x-table.td>
				@cando('nomer-zakaza-(klient):site')<x-table.td class="w8rem-4px"><strong>№ заказа</strong></x-table.td> @endcando
				@cando('data-(klient):site')<x-table.td class="w18rem"><strong>Дата</strong></x-table.td> @endcando
				@cando('dannye-(klient):site')<x-table.td class="w-auto"><strong>Данные</strong></x-table.td> @endcando
				@cando('kommentariy-(klient):site')<x-table.td class="w-25rem"><strong>Комментарий</strong></x-table.td> @endcando
				@cando('stoimost-(klient):site')<x-table.td class="w-7rem h-end" title="Стоимость"><strong>$</strong></x-table.td> @endcando
				
				@cando('ssylka-(klient):site')<x-table.td class="w4rem h-center" title="Ссылка"><i class="fa-solid fa-fw fa-link"></i></x-table.td> @endcando
				
				<x-table.td class="w-24rem" title="Дата и время события"><strong>Дата и время события</strong></x-table.td>
				<x-table.td class="w-10rem" title="Дата и время события"><strong>Команда</strong></x-table.td>
				
				@if(auth('site')->user()->can('dannye-(klient):site') && $type == 'actual')
					<x-table.td class="w-spacer p-0"></x-table.td>
				@elseif($type == 'actual')
					<x-table.td class="w-auto p-0"></x-table.td>
				@endif
				
				@if(auth('site')->user()->can('deystviya-(klient):site') && $type == 'actual')
					<x-table.td class="h-center w-8rem"><strong>Действия</strong></x-table.td>
				@endif
			</x-table.tr>
		</x-table.head>
		<x-table.body class="minh-4rem" emptytext="Нет заказов">
			@foreach ($list as $order)
				@include($itemView, $order)
			@endforeach
		</x-table.body>
	</x-table>
@else
	<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>
@endif