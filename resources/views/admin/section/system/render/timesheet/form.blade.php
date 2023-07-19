<x-input-group size="normal" id="">
	<div class="row row-cols-1 gy-20 mb2rem">
		<div class="col">
			<p class="color-gray-600 fz12px mb3px">Название команды</p>
			<x-select
				name="command_id"
				class="w100"
				:options="$data['commands']"
				choose="Команда не выбрана"
				empty="Нет команд"
				empty-has-value
				value="{{$command_id ?? null}}"
				/>
		</div>
		<div class="col">
			<p class="color-gray-600 fz12px mb3px">Тип события</p>
			<x-select
				name="event_type_id"
				class="w100"
				:options="$data['events_types']"
				choose="Тип события не выбран"
				empty="Нет типов событий"
				empty-has-value
				value="{{$event_type_id ?? null}}"
				/>
		</div>
		<div class="col">
			<div class="row row-cols-2 justify-content-between">
				<div class="col">
					<p class="color-gray-600 fz12px mb3px">Дата</p>
					<x-datepicker name="date" date="{{isset($datetime) ? DdrDateTime::date($datetime, ['format' => 'YYYY-MM-DD']) : null}}" class="w100" />
				</div>
				<div class="col-auto">
					<p class="color-gray-600 fz12px mb3px">Время (МСК)</p>
					<x-input
						type="time"
						name="time"
						value="{{isset($datetime) ? DdrDateTime::time($datetime) : null}}"
						class="w100" />
				</div>
			</div>
		</div>
	</div>
</x-input-group>