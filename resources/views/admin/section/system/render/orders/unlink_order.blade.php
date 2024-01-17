<div class="row row-cols-1 gy-10 mb2rem">
	<div class="col">
		<p class="color-green fz16px">Отвязать и перенести в:</p>
	</div>
	<div class="col">
		<x-select
			class="w20rem"
			id="listType"
			size="normal"
			:options="$lists"
			action="detachOrderChangeListType"
			/>
	</div>
	<div class="col">
		<div class="w20rem d-inline-block" id="detachWaitGroupBlock" hidden>
			<p class="text-start">Выбрать группу:</p>
			<x-select
				class="w100"
				id="waitGroupSelect"
				size="normal"
				:options="$waitListGroups"
				empty-has-value
				/>
		</div>
			
	</div>
</div>

