<x-table id="eventLogTable" class="w100" scrolled="calc(100vh - 224px)" noborder>
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w20rem v-end" noborder><strong class="fz12px lh90">Пользователь</strong></x-table.td>
			<x-table.td class="w30rem v-end" noborder><strong class="fz12px lh90">Действие</strong></x-table.td>
			
			<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">ID Заказа</strong></x-table.td>
			<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">Номер заказа</strong></x-table.td>
			{{-- <x-table.td class="w-8rem v-end" noborder><strong class="fz12px lh90">Стоимость</strong></x-table.td>
			<x-table.td class="w-8rem v-end" noborder><strong class="fz12px lh90">Команда</strong></x-table.td>
			<x-table.td class="w-8rem v-end" noborder><strong class="fz12px lh90">Ссылка</strong></x-table.td>
			<x-table.td class="w-8rem v-end" noborder><strong class="fz12px lh90">Инвайт</strong></x-table.td>
			<x-table.td class="w-12rem v-end" noborder><strong class="fz12px lh90">Данные</strong></x-table.td>
			<x-table.td class="w-auto v-end" noborder><strong class="fz12px lh90">Дата и время</strong></x-table.td>
			<x-table.td class="w-6rem v-end" noborder><strong class="fz12px lh90">Врем. зона</strong></x-table.td>
			<x-table.td class="w-8rem v-end" noborder><strong class="fz12px lh90">Тип события</strong></x-table.td>
			<x-table.td class="w-auto v-end" noborder><strong class="fz12px lh90">Период</strong></x-table.td> --}}
			
			<x-table.td class="w-auto" noborder></x-table.td>
			<x-table.td class="w-9rem v-end" noborder><strong>Подробно</strong></x-table.td>
			<x-table.td class="w18rem v-end" noborder><strong class="fz12px lh90">Дата и время</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($data as $item)
			<x-table.tr class="h4rem">
				<x-table.td>
					<p
						@class([
							'fz12px',
							'color-red' => $item['user_type'] == 2,
							'color-green' => $item['user_type'] == 1,
						])
						>{{($item['from']['name'] ?? 'Неизвестный'.($item['user_type'] == 1 ? ' пользователь' : ' админ')) ?: ($item['from']['pseudoname'] ?? 'Неизвестный'.($item['user_type'] == 1 ? ' пользователь' : ' админ'))}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['event_type'] ?? '-'}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['id']['data'] ?? '-'}}</p>
				</x-table.td>
				<x-table.td>
					@if($item['info']['order']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['order']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['order']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['order']['data'] ?? '-'}}</p>
					@endif
				</x-table.td>
				
				{{-- <x-table.td>
					@if($item['info']['price']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['price']['data'] ?? null}} @symbal(rub)</p>
						<p class="color-green fz12px">{{$item['info']['price']['updated'] ?? null}} @symbal(rub)</p>
					@else
						<p class="fz12px">{{$item['info']['price']['data']}} @symbal(rub)</p>
					@endif
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['command']['data']}}</p>
				</x-table.td>
				<x-table.td>
					@if($item['info']['link']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['link']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['link']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['link']['data']}}</p>
					@endif
				</x-table.td>
				<x-table.td>
					@if($item['info']['server_name']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['server_name']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['server_name']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['server_name']['data']}}</p>
					@endif
				</x-table.td>
				<x-table.td>
					@if($item['info']['raw_data']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['raw_data']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['raw_data']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['raw_data']['data']}}</p>
					@endif
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['date']['data']}}</p>
					<p class="fz12px">{{$item['info']['date_msc']['data']}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['timezone']['data']}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['event_type']['data']}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['timesheet_period']['data']}}</p>
				</x-table.td> --}}
				
				<x-table.td></x-table.td>
				<x-table.td class="h-center">
					<x-buttons-group group="small" w="3rem">
						<x-button variant="blue" action="eventsLogsInfo:{{$item['id']}}" title="Подробная информация">
							<i class="fa-solid fa-ац fa-info"></i>
						</x-button>
					</x-buttons-group>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{DdrDateTime::date($item['datetime'] ?? null, ['shift' => true])}} в {{DdrDateTime::time($item['datetime'] ?? null, ['shift' => true])}}</p>
				</x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
</x-table>