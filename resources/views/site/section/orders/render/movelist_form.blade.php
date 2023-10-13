<div class="row row-cols-1 gy-20 mb2rem">
	<div class="col">
		<p class="fz16px color-green">Вы действительно хотите перенести заказ в {{$listType ?? '---'}}?</p>
	</div>

	<div class="col">
		<p class="color-gray-500 fz14px mb3px text-start">Комментарий:</p>
		<x-textarea size="small" id="comment" class="w100" rows="3" placeholder="Введите текст" noresize />
	</div>
</div>