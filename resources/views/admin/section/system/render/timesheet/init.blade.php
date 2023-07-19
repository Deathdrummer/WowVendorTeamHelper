<x-table class="w100" id="timesheetTable" noborder scrolled="68vh">
	<x-table.head>
		<x-table.tr noborder>
			<x-table.td class="w20rem" noborder><strong>Дата (МСК)</strong></x-table.td>
			<x-table.td class="w12rem" noborder><strong>Время (МСК)</strong></x-table.td>
			<x-table.td class="w-auto" noborder><strong>Название команды</strong></x-table.td>
			<x-table.td class="w30rem" noborder><strong>Тип события</strong></x-table.td>
			<x-table.td class="w7rem" noborder><strong>Кол-во заказов</strong></x-table.td>
			<x-table.td class="w12rem" noborder><strong>Действия</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body id="timesheetList" class="minh-4rem" emptytext="Нет записей"></x-table.body>
</x-table>