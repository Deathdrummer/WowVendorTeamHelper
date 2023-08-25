<x-input-group group="normal">
	<tr class="h6rem" index="{{$index}}">
		<td>
			<x-input name="name" value="{{isset($name) ? $name : ''}}" class="w100" placeholder="Имя" />
		</td>
		<td>
			<x-input name="pseudoname" value="{{isset($pseudoname) ? $pseudoname : ''}}" class="w100" placeholder="Псевдоним сотрудника" />
		</td>
		<td>
			<x-input name="email" value="{{isset($email) ? $email : ''}}" class="w100"  placeholder="E-mail сотрудника" />
		</td>
		<td>
			<x-select
				name="role"
				class="w100"
				:options="$data['roles']"
				choose="Роль не выбрана"
				empty="Нет ролей"
				no-choose-has-value 
				/>
		</td>
		<td></td>
		<td class="center"><span class="color-gray">-</span></td>
		<td class="right">
			<x-buttons-group group="small" class="mr3px" w="3rem" gx="6" inline>
				<x-button variant="blue" action="usersSave" title="Сохранить" disabled save><i class="fa-solid fa-floppy-disk"></i></x-button>
				<x-button variant="red" action="usersRemove" title="Удалить"><i class="fa-solid fa-trash-can"></i></x-button>
			</x-buttons-group>
		</td>
	</tr>
</x-input-group>