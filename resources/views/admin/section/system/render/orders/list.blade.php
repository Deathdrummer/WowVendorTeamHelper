@if(count($list))
	<x-table class="w100" scrolled="300px">
		<x-table.head>
			<x-table.tr class="h3rem">
				<x-table.td class="w4rem h-center"><strong>№</strong></x-table.td>
				<x-table.td class="w8rem-4px"><strong>№ заказа</strong></x-table.td>
				<x-table.td class="w19rem"><strong>Дата</strong></x-table.td>
				<x-table.td class="w-auto"><strong>Данные</strong></x-table.td>
				<x-table.td class="w16rem"><strong>Имя сервера</strong></x-table.td>
				<x-table.td class="w-30rem"><strong>Комментарий</strong></x-table.td>
				<x-table.td class="w-9rem"><strong>Стоимость</strong></x-table.td>
				<x-table.td
					@class([
						'w-10rem' => $showType['text'] ?: false,
						'w-5rem' => ($showType['color'] || $showType['icon']) ?: false,
					])
					>
					<strong>{{$showType['text'] ? 'Статус' : 'Стат'}}</strong>
				</x-table.td>
				<x-table.td class="w4rem h-center" title="Ссылка"><i class="fa-solid fa-fw fa-link"></i></x-table.td>
				<x-table.td class="w-10rem"><strong>Действия</strong></x-table.td>
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