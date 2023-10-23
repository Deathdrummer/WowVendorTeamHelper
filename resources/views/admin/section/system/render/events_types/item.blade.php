<x-input-group size="small">
	<x-table.tr class="h4rem">
		<x-table.td>
			<x-input name="title" value="{{$title ?? '-'}}" class="w100"  placeholder="Название команды" />
		</x-table.td>
		<x-table.td>
			<x-select
				name="difficult_id"
				class="w100"
				:options="$data['difficulties'] ?? []"
				choose="Сложность не выбрана"
				empty="Нет сложностей"
				value="{{$difficult_id}}"
				/>
		</x-table.td>
		<x-table.td class="h-center">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="blue" action="eventTypeUpdate:{{$id}}" title="Сохранить" disabled update><i class="fa-solid fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="eventTypeRemove:{{$id}}" title="Удалить"><i class="fa-solid fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>