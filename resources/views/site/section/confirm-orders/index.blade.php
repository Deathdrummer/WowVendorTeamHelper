<section>
	<x-chooser
		variant="neutral"
		size="normal"
		px="20"
		py="5"
		class="mb1rem"
		id="listTypeChooser"
		>
		<x-chooser.item
			action="setListTypeAction:actual"
			active
			listtypechooser="actual"
			>Актуальные
		</x-chooser.item>
		<x-chooser.item
			action="setListTypeAction:past"
			listtypechooser="past"
			>Подтвержденные
		</x-chooser.item>
	</x-chooser>


	<div id="timesheetContainer" class="timesheetcontainer pt2rem"><p class="color-gray-400 fz16px noselect text-center">Выберите период</p></div>
</section>
	