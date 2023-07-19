<x-input-group size="small">
	<x-table.tr index="{{$index}}" class="h4rem">
		<x-table.td>
			<x-input name="title" class="w100"  placeholder="Название события" />
		</x-table.td>
		<x-table.td>
			<x-select
				name="difficult_id"
				class="w100"
				:options="$data['difficulties']"
				choose="Не выбрана"
				empty="Нет сложностей"
				empty-has-value
				/>
		</x-table.td>
		<x-table.td class="h-center">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="blue" action="eventTypeSave" title="Сохранить" disabled save><i class="fa-solid fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="eventTypeRemove" title="Удалить"><i class="fa-solid fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>