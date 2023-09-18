<x-table id="eventLogTable" class="w100" scrolled="calc(100vh - 224px)" noborder>
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w16rem v-end" noborder><strong class="fz12px lh90">Пользователь</strong></x-table.td>
			<x-table.td class="w16rem v-end" noborder><strong class="fz12px lh90">Действие</strong></x-table.td>
			<x-table.td class="w-7rem v-end" noborder><strong class="fz12px lh90">ID события</strong></x-table.td>
			<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">Период</strong></x-table.td>
			<x-table.td class="w-10rem v-end" noborder><strong class="fz12px lh90">Команда</strong></x-table.td>
			<x-table.td class="w-20rem v-end" noborder><strong class="fz12px lh90">Тип события</strong></x-table.td>
			<x-table.td class="w-auto v-end" noborder><strong class="fz12px lh90">Дата и время события</strong></x-table.td>
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
					<p class="fz12px">{{$item['event_type']}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['id']['data']}}</p>
				</x-table.td>
				<x-table.td>
					<p class="fz12px">{{$item['info']['timesheet_period_id']['data']}}</p>
				</x-table.td>
				<x-table.td>
					@if($item['info']['command_id']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['command_id']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['command_id']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['command_id']['data']}}</p>
					@endif
				</x-table.td>
				<x-table.td>
					@if($item['info']['event_type_id']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{$item['info']['event_type_id']['data'] ?? null}}</p>
						<p class="color-green fz12px">{{$item['info']['event_type_id']['updated'] ?? null}}</p>
					@else
						<p class="fz12px">{{$item['info']['event_type_id']['data']}}</p>
					@endif
				</x-table.td>
				<x-table.td>
					@if($item['info']['datetime']['updated'] ?? null)
						<p class="color-red text-crossed fz12px">{{DdrDateTime::date($item['info']['datetime']['data'] ?? null).' в '.DdrDateTime::time($item['info']['datetime']['data'] ?? null) ?? '-'}}</p>
						<p class="color-green fz12px">{{DdrDateTime::date($item['info']['datetime']['updated'] ?? null).' в '.DdrDateTime::time($item['info']['datetime']['updated'] ?? null) ?? '-'}}</p>
					@else
						<p class="fz12px">{{DdrDateTime::date($item['info']['datetime']['data'] ?? null).' в '.DdrDateTime::time($item['info']['datetime']['data'] ?? null) ?? '-'}}</p>
					@endif
				</x-table.td>
				
				{{-- ID события
				Период
				Команда
				Тип события
				Дата и время события --}}
				
				
				
				<x-table.td>
					<p class="fz12px">{{DdrDateTime::date($item['datetime'] ?? null, ['shift' => true])}} в {{DdrDateTime::time($item['datetime'] ?? null, ['shift' => true])}}</p>	
				</x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
</x-table>