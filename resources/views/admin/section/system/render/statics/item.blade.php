<x-input-group size="small">
	<x-table.tr class="h4rem">
		<x-table.td>
			<x-input name="title" value="{{$title ?? '-'}}" class="w100"  placeholder="Название команды" />
		</x-table.td>
		<x-table.td>
			<x-input type="color" name="color" value="{{$color ?? '-'}}" class="w100" />
		</x-table.td>
		<x-table.td>
			<x-select
				name="region_id"
				class="w100"
				:options="$data['timezones']"
				choose="Регион не выбран"
				empty="Нет регионов"
				value="{{$region_id}}"
				/>
		</x-table.td>
		<x-table.td class="h-center">
			<x-buttons-group group="small" w="3rem">
				<x-button variant="blue" action="commandUpdate:{{$id}}" title="Сохранить" disabled update><i class="fa-solid fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="commandRemove:{{$id}}" title="Удалить"><i class="fa-solid fa-trash-can"></i></x-button>
			</x-buttons-group>
		</x-table.td>
	</x-table.tr>
</x-input-group>