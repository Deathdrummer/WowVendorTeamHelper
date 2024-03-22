<x-input-group size="small">
	<x-table.tr index="{{$index}}" class="h4rem">
		<x-table.td>
			<x-input name="title" class="w100" placeholder="Название команды" />
		</x-table.td>
		<x-table.td>
			<x-input name="webhook" class="w100" placeholder="Вебхук" />
		</x-table.td>
		<x-table.td>
			<x-input type="color" name="color" class="w100" />
		</x-table.td>
		<x-table.td>
			<x-select
				name="region_id"
				class="w100"
				:options="$data['timezones']"
				choose="Регион не выбран"
				empty="Нет регионов"
				empty-has-value
				/>
		</x-table.td>
		<x-table.td class="h-center">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="blue" action="commandSave" title="Сохранить" disabled save><i class="fa-solid fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="commandRemove" title="Удалить"><i class="fa-solid fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>