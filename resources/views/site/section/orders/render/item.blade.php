<li
	@class([
		'ddrlist__item',
		'order',
		'order_new' => isset($new),
		'border-all',
		'border-gray-200',
		'border-radius-5px',
		'mr5px',
	])
	order="{{$id ?? null}}"
	>
	<div class="col-auto">
		<div class="order__block order__id">
			<strong>{{$id ?? null}}</strong>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			<div class="order__ordernumber">
				<p>{{$order ?? '-'}}</p>
			</div>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			<div class="order__datetime">
				<p class="pointer" title="Кликните для копирования">
					<strong class="w3rem d-inline-block text-end">ориг</strong>:
					@if($date)
						@if(isset($timezones[$timezone_id]['format_24']) && $timezones[$timezone_id]['format_24'])
							<span class="color-blue-hovered color-black-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date, ['format' => 'DD.MM.YY HH:mm'])}} {{$timezones[$timezone_id]['timezone']}}</span>
						@else
							<span class="color-blue-hovered color-black-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date, ['locale' => 'en', 'format' => 'DD.MM.YY h:mm A'])}} {{$timezones[$timezone_id]['timezone']}}</span>
						@endif
					@else
						<span class="color-gray">-</span>
					@endif
				</p>
				
				<p class="pointer" title="Кликните для копирования">
					<strong class="w3rem d-inline-block text-end">мск</strong>:
					@if($date)
						<span class="color-blue-hovered color-black-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date_msc, ['locale' => 'en', 'format' => 'DD.MM.YY HH:mm'])}} МСК</span>
					@else
						<span class="color-gray">-</span>
					@endif
				</p>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="order__block">
			<p class="order__content">{{$raw_data ?? 'нет данных'}}</p>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			<div class="order__price">
				@if($price)
					<p>@number($price) @symbal(dollar)</p>
				@else
					<p class="color-gray">-</p>
				@endif
			</div>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			{{-- <a target="_blank" href="{{$link ?? '#'}}">{{$link ?? 'Нет ссылки'}}</a> --}}
			<x-button
				class="order__link"
				disabled="{{!$link ?? true}}"
				title="{{$link ? 'Перейти' : 'Нет ссылки'}}"
				size="small"
				w="50px"
				variant="purple"
				action="openLink:{{$link ?? null}}"
				><i class="fa-solid fa-fw fa-link"></i></x-button>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block order__block-noborder">
			<x-buttons-group class="order__right" size="small">
				<p>{{$status}} {{App\Enums\OrderStatus::new}}</p>
				{{-- @if(!isset($status) || $status == App\Enums\OrderStatus::new)
					<x-button w="50px" action="toCancelListBtn:{{$id ?? null}}" variant="red"><i class="fa-solid fa-fw fa-ban"></i></x-button>
					<x-button w="50px" action="toWaitListBtn:{{$id ?? null}}" variant="blue"><i class="fa-solid fa-fw fa-hourglass-half"></i></x-button>
				@elseif($status == App\Enums\OrderStatus::wait)
					<x-button w="50px" action="toCancelListBtn:{{$id ?? null}}" variant="red"><i class="fa-solid fa-fw fa-ban"></i></x-button>
				@elseif($status == App\Enums\OrderStatus::cancel)
					
				@endif --}}
				<x-button w="50px" variant="green" action="toTimesheetBtn:{{$id ?? null}},{{$date ?? null}},{{$order ?? '-'}}"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
			</x-buttons-group>
		</div>
	</div>
</li>