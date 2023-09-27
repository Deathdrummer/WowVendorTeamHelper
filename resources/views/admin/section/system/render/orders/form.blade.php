<x-input-group size="normal">
	@if($action == 'new')
		<input type="hidden" name="timezone_id" value="{{$timezone['id'] ?? null}}">
		<input type="hidden" name="date" value="{{$date ?? null}}">
	@endif
	
	<div class="row row-cols-1 gy-20 mb2rem">
		<div class="col">
			<div class="row gx-20">
				<div class="col-auto">
					<p class="color-gray-600 fz12px mb3px">Номер заказа:</p>
					<x-input name="order" value="{{$order ?? null}}" class="w14rem" placeholder="Номер заказа" />
				</div>
				<div class="col-auto">
					<p class="color-gray-600 fz12px mb3px">Тип заказа:</p>
					<x-select name="order_type" :options="$ordersTypes" value="{{$order_type ?? null}}" empty="" choose="Не выбран" choose-empty class="w14rem" />
				</div>
				<div class="col-auto">
					<p class="color-gray-600 fz12px mb3px">Стоимость @symbal(dollar):</p>
					<x-input name="price" value="{{$price ?? null}}" class="w10rem" id="orderFormPrice" placeholder="Стоимость" />
				</div>
				<div class="col">
					<p class="color-gray-600 fz12px mb3px">Инвайт:</p>
					<x-input name="server_name" value="{{$server_name ?? null}}" class="w100" placeholder="Инвайт /inv" />
				</div>
			</div>
		</div>
		<div class="col">
			<p class="color-gray-600 fz12px mb3px">Тело заказа:</p>
			<x-textarea name="raw_data" id="newOrderRawData" value="{{$raw_data ?? null}}" class="w100" rows="5" placeholder="Введите текст" noresize />
		</div>
		
		@if($action == 'new')
			<div class="col">
				<p class="color-gray-600 fz12px mb3px">Комментарий:</p>
				<x-textarea name="comment" class="w100" rows="3" placeholder="Введите текст" noresize />
			</div>
		@endif
		
		<div class="col">
			<p class="color-gray-600 fz12px mb3px">Ссылка:</p>
			<x-input name="link" value="{{$link ?? null}}" class="w100" placeholder="Введите текст" />
		</div>
	</div>
</x-input-group>