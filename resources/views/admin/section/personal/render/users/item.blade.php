<x-input-group group="normal">
<tr class="h6rem">
	<td>
		<strong class="color-black">{{isset($name) ? $name : __('custom.anon')}}</strong>
	</td>
	<td>
		<strong class="color-black">{{isset($pseudoname) ? $pseudoname : __('custom.anon')}}</strong>
	</td>
	<td>
		<p class="color-black">{{isset($email) ? $email : ''}}</p>
	</td>
	<td>
		<x-select
			name="role"
			class="w100"
			:options="(!$hasRoles && $hasPermissions) ? ($data['roles_custom'] ?? []) : ($data['roles'] ?? [])"
			choose="{{(!$hasRoles && $hasPermissions) ? '' : 'Роль не выбрана'}}"
			empty="Нет ролей"
			empty-has-value
			value="{{$hasRoles ? $roles[0]['id'] : null}}"
			/>
	</td>
	<td></td>
	<td class="center">
		@if(isset($email_verified_at) && $email_verified_at)
			<i class="fa-solid fa-check color-green"></i>
		@else
			<i class="fa-solid fa-ban color-red"></i>
		@endif
	</td>
	<td class="center">
		<x-buttons-group group="small" w="3rem" gx="6" inline>
			<x-button
				variant="light"
				group="small"
				action="userSettings:{{$id}}"
				class="px-0 w3rem"
				title="Настройки пользователи"
				>
				<i class="fa-solid fa-cog"></i>
			</x-button>
			<x-button
				variant="{{$temporary_password ? 'green' : 'light'}}"
				group="small"
				action="usersSendEmail:{{$id}}"
				class="px-0 w3rem"
				title="{{$temporary_password ? 'Выслать доступ сотруднику' : 'Выслать доступ повторно'}}"
				>
				<i class="fa-solid fa-envelope"></i>
			</x-button>
			<x-button
				variant="green"
				group="small"
				action="usersSetRules:{{$id}},{{isset($pseudoname) ? $pseudoname : __('custom.anon')}}"
				class="px-0 w3rem"
				title="Права пользователя"
				>
				<i class="fa-solid fa-list-check"></i>
			</x-button>
			<x-button
				variant="blue"
				action="usersUpdate"
				title="Сохранить"
				disabled
				update="{{isset($id) ? $id : ''}}"
				><i class="fa-solid fa-floppy-disk"></i></x-button>
			<x-button
				variant="red"
				action="usersRemove"
				title="Удалить"
				remove="{{isset($id) ? $id : ''}}"
				><i class="fa-solid fa-trash-can"></i></x-button>
		</x-buttons-group>
	</td>
</tr>
</x-input-group>