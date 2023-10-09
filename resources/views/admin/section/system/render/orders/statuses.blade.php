@if($orderStatusesSettings)
	<ul orderstatusestooltip>
		@foreach($orderStatusesSettings as $statusName => ['name' => $name, 'icon' => $icon, 'color' => $color])
			@cando($statusName.'-status:site')
				<li @class([
					'statusitem',
					'statusitem-active'	=> $currentStatusName == $statusName,
				])
				ordertatus
				onclick="$.setOrderStatus(this, '{{$statusName}}', {{$currentStatusName == $statusName ? 1 : 0}})"
				>
					@if($showType == 'color')
						<div class="w2rem h2rem statusitem__color" style="background-color: {{$color}};"></div>
					@elseif($showType == 'icon')
						<div class="statusitem__icon">
							<i class="fa-solid fa-fw fa-{{$icon}}" style="color: {{$color}};"></i>
						</div>
					@endif
					
					<p class="statusitem__name">{{$name}}</p>
				</li>
			@endcando
		@endforeach
	</ul>
@else
	<p class="color-gray text-center">Нет статусов</p>
@endif