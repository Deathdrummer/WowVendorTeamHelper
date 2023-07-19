<x-input-group size="small">
	<x-table.tr index="{{$index}}" class="h4rem">
		<x-table.td>
			<x-input name="title" class="w100"  placeholder="Название команды" />
		</x-table.td>
		<x-table.td>
			 <p>-</p>
		</x-table.td>
		<x-table.td class="h-end">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="blue" action="timesheetPeriodsSave" title="Сохранить" disabled save><i class="fa-solid fa-fw fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="timesheetPeriodsRemove" title="Удалить"><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>