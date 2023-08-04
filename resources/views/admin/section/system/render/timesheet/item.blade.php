<x-table.tr class="h4rem pointer noselect bg-light-hovered" onclick="$.timesheetGetOrders(this, {{$id}})">
	<x-table.td>
		<p>{{DdrDateTime::date($datetime)}}</p>
	</x-table.td>
	<x-table.td>
		<p>
			{{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}
			/
			@if(isset($data['commands'][$command_id]['format_24']) && $data['commands'][$command_id]['format_24'])
				{{DdrDateTime::time($datetime, ['format' => 'HH:mm', 'shift' => $data['commands'][$command_id]['shift']])}} {{$timezones[$timezone_id]['timezone']}}
			@else
				{{DdrDateTime::time($datetime, ['locale' => 'en', 'format' => 'h:m A', 'shift' => $data['commands'][$command_id]['shift']])}} {{$data['commands'][$command_id]['timezone']}}
			@endif
		</p>
	</x-table.td>
	<x-table.td>
		<p>{{$data['commands'][$command_id]['region_id'] ?? '-'}}</p>
	</x-table.td>
	<x-table.td>
		<p>{{$data['events_types'][$event_type_id] ?? '-'}}</p>
	</x-table.td>
	<x-table.td class="h-center">
		<p orderscount>{{$orders_count}}</p>
	</x-table.td>
	<x-table.td class="h-center" timesheetrulesblock>
		<x-buttons-group group="small" w="3rem" gx="5">
			<x-button variant="green" action="timesheetNewOrder:{{$id}}" title="Добавить заказ"><i class="fa-solid fa-fw fa-plus"></i></x-button>
			<x-button variant="blue" action="timesheetEdit:{{$id}}" title="Изменить"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
			<x-button variant="red" action="timesheetRemove:{{$id}}" title="Удалить"><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
		</x-buttons-group>
	</x-table.td>
</x-table.tr>