@foreach($data as $item)
	<x-table.tr class="h4rem">
		<x-table.td class="fz12px">
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