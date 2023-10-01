<x-input-group size="small">
	<x-table.tr class="h4rem">
		<x-table.td>
			<p>{{$title ?? ''}}</p>
		</x-table.td>
		<x-table.td class="h-center">
			 <strong>{{$timesheet_items_count ?? 0}}</strong>
		</x-table.td>
		<x-table.td class="h-end">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="purple" action="timesheetPeriodsWinBuild:{{$id}},{{$timesheet_items_count ?? 0 ? 1 : 0}}"{{-- {{!count($timesheet_items) ? ' disabled' : ''}} --}} title="Сформировать записи периода"><i class="fa-solid fa-fw fa-list-ul"></i></x-button>
				<x-button variant="red" action="timesheetPeriodsRemove:{{$id}}" disabled="{{$timesheet_items_count ?? 0 > 0}}" title="Удалить"><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>

