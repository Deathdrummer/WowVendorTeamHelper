@foreach($timesheets as ['id' => $id, 'datetime' => $datetime, 'command_id' => $command_id, 'event_type_id' => $event_type_id, 'orders_count' => $orders_count])
	<x-table.tr
		@class([
			'h3rem',
			'pointer',
			'noselect',
			'timesheetitem',
			'highlighting' => $orderDate == $datetime,
			'active' => $choosedTsId == $id,
		])
		onmousedown="$.relocateOrderChooseTs(this, this.classList.contains('active'), {{$id}})"
		>
		<x-table.td>
			<p class="fz12px">{{DdrDateTime::date($datetime)}}</p>
		</x-table.td>
		<x-table.td>
			<p class="fz12px">{{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}</p>
		</x-table.td>
		<x-table.td>
			<p class="fz12px">{{DdrDateTime::time($datetime?->addHours($regionShiftHours), ['format' => ['en' => 'h:i a']])}}</p>
		</x-table.td>
		<x-table.td>
			<p class="fz12px">{{$commands[$command_id] ?? '-'}}</p>
		</x-table.td>
		<x-table.td>
			<p class="fz12px">{{$eventsTypes[$event_type_id] ?? '-'}}</p>
		</x-table.td>
		<x-table.td class="h-center">
			<p class="fz12px">{{$orders_count}}</p>
		</x-table.td>
	</x-table.tr>
@endforeach