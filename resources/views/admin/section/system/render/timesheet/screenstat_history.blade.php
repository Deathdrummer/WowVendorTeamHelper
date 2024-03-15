<div class="screenstathistory ddrlist">
	@forelse($history as $hItem)
		<div class="screenstathistory__item ddrlist__item mt10px">
			
			<div class="row align-items-start mb1rem">
				<div class="col">
					<p
						@class([
							'color-red'	=> $hItem['user_type'] == 'admin',
							'color-green'	=> $hItem['user_type'] == 'site'
						])
						title="{{$hItem['user_type'] == 'admin' ? 'Администратор' : 'Пользователь'}}"
					>
					@if($hItem['user_type'] == 'admin')
						@if($myAdminId == $hItem['from_id'])
							Я
						@else
							{{$adminUsers[$hItem['from_id']] ?? 'Неизвестный админ'}}
						@endif
					@elseif($hItem['user_type'] == 'site')
						@if($mySiteId == $hItem['from_id'])
							Я
						@else
							{{$siteUsers[$hItem['from_id']] ?? 'Неизвестный пользователь'}}
						@endif
					@endif
					</p>
				</div>
				<div class="col-auto">
					<p class="screenstathistory__date">{{DdrDateTime::date($hItem['date_add'], ['shift' => '+'])}} в {{DdrDateTime::time($hItem['date_add'], ['shift' => '+'])}}</p>
				</div>
				<div class="col-auto">
					@if($hItem['send_to_slack'])
						<i class="fa-solid fa-fw fa-check color-green screenstathistory__sendstatus" title="отправлено в Slack"></i>
					@else
						<i class="fa-solid fa-fw fa-ban color-red screenstathistory__sendstatus" title="НЕ отправлено в Slack"></i>
					@endif
				</div>
			</div>
			
			{{-- <p>{{$hItem['id']}}</p> --}}
			
			<x-horisontal space="1rem" ignore="[noscroll], select, input, textarea" track="3px" ignoremovekeys="alt, ctrl, shift" style="max-width: 606px;">
				@forelse($sortedOrdersTypes as $otId => $otTitle)
					@if(!isset($hItem['stat'][$otId]))
						@continue
					@endif
					<x-horisontal.item stretch="false" class="h100">
						<div class="screenstathistory__statitem">
							<p class="screenstathistory__title">{{$otTitle}}</p>
							<p class="screenstathistory__count">клиетов: <span>{{$hItem['stat'][$otId]['count']}}</span></p>
							
							<hr class="hr-light mt5px mb5px">
							
							<p class="fz12px color-gray-500">Список заказов:</p>
							<ul class="screenstathistory__list" noscroll >
								@forelse($hItem['stat'][$otId]['items'] as $order)
									<li select-text>{{$order}}</li>
								@empty
									<li class="color-gray-500">-</li>
								@endforelse
							</ul>
						</div>
					</x-horisontal.item>
				@empty
				@endforelse
			</x-horisontal>
			
			@if($hItem['screenshot'] ?? false)
				<div class="screenstathistory__screenshot" onclick="$.openOrigScreenshot('{{$hItem['screenshot']}}')">
					<img src="{{$hItem['thumb']}}" alt="{{$otTitle}}" title="{{$otTitle}}">
				</div>
			@endif
		</div>
	@empty
		<p class="color-gray-500 text-center">Нет данных</p>
	@endforelse
</div>