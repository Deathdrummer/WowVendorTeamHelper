<x-input-group size="small">
	<x-table.tr class="h4rem">
		<x-table.td>
			<p>{{$title ?? ''}}</p>
		</x-table.td>
		<x-table.td class="h-center">
			 <strong>{{$timesheet_items_count ?? 0}}</strong>
		</x-table.td>
		<x-table.td
			@class([
				'h-end' => !$ordersCountsStat && !$accounting,
				'h-center' => $ordersCountsStat || $accounting,
			])
			>
			<x-buttons-group group="small" w="3rem">
				@if($ordersCountsStat)
					<x-button
						variant="yellow"
						action="timesheetPeriodsBuild:{{$id}}"
						disabled="{{!($timesheet_items_count ?? false)}}"
						title="Сформировать отчет"
						><i class="fa-solid fa-fw fa-chart-column"></i></x-button>
				@elseif($accounting)
					<x-checkbox
						input-accountingperiod="{{$id}}"
						/>
					{{-- <x-button
						variant="yellow"
						action="timesheetPeriodsBuild:{{$id}}"
						disabled="{{!($timesheet_items_count ?? false)}}"
						title="Сформировать отчет"
						><i class="fa-solid fa-fw fa-chart-column"></i></x-button> --}}
				@else
					<x-button
						variant="purple"
						action="timesheetPeriodsWinBuild:{{$id}},{{$timesheet_items_count ?? 0 ? 1 : 0}}"
						title="Сформировать записи периода"
						><i class="fa-solid fa-fw fa-list-ul"></i></x-button>
					
					<x-button
						variant="red"
						action="timesheetPeriodsRemove:{{$id}}"
						disabled="{{$timesheet_items_count ?? 0 > 0}}"
						title="Удалить"
						><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
				@endif
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>

