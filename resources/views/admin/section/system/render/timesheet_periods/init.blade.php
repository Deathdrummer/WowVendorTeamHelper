@unless($ordersCountsStat)
	<div class="d-flex justify-content-end mb2rem">
		<x-button
			variant="blue"
			size="small"
			action="timesheetPeriodsAddBtnAction"
			>Добавить период</x-button>
	</div>
@endif

<x-table class="w100" id="timesheetPeriodsTable" noborder scrolled="60vh">
	<x-table.head>
		<x-table.tr noborder>
			<x-table.td class="w-auto" noborder><strong>Название периода</strong></x-table.td>
			<x-table.td class="w9rem" noborder><strong>Кол-во событий</strong></x-table.td>
			<x-table.td class="w9rem" noborder><strong>Действия</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body id="timesheetPeriodsList" class="minh-4rem" emptytext="Нет записей"></x-table.body>
</x-table>