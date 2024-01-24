<div class="row row-cols-1 gy-20 mb2rem">
	<div class="col">
		<p class="fz16px color-green">Вы действительно хотите перенести заказ{{$multiple ? 'ы' : ''}} в {{$listType ?? '---'}}?</p>
	</div>
	
	@if($waitListGroups ?? false)
		<div class="col text-start">
			<p class="color-gray-500 fz14px mb3px text-start">Группа:</p>
			<x-select
				id="groupId"
				size="small"
				:options="$waitListGroups"
				empty="Нет данных!"
				choose="Не выбрана"
				empty-has-value 
				group="small"
				class="w50"
				/>
		</div>
	@endif
	
	{{-- <div class="col text-start">
		<x-checkbox
			size="small"
			label="dfsdfsd"
			/>
	</div> --}}
	
	<div class="col">
		<p class="color-gray-500 fz14px mb3px text-start">Комментарий:</p>
		<x-textarea size="small" id="comment" class="w100" rows="3" placeholder="Введите текст" noresize />
	</div>
</div>