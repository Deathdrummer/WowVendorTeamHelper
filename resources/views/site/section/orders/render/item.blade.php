<li
	@class([
		'ddrlist__item',
		'order',
		'order_new' => isset($new),
		//'order_doprun' => isset($is_doprun) && $is_doprun,
		'border-all',
		'border-gray-200',
		'border-radius-5px',
		'mr5px',
	])
	@if($is_doprun ?? false) style="border-color: {{$doprunStatus['color'].'5e' ?? null}};"@endif
	order="{{$id ?? null}}"
	>
	<div class="col-auto">
		<div class="order__block order__choose">
			<x-checkbox
				tag="choosedorder:{{$id ?? null}}"
				size="small"
				/>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block order__id">
			<strong>{{$id ?? null}}</strong>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			<div class="order__ordernumber">
				<p
					class="color-gray-500 color-gray-600-hovered pointer color-green-active"
					onclick="$.copyToClipboard(event, '{{$order ?? '-'}}')" title="Скопировать"
					>{{$order ?? '-'}}</p>
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
							<span class="color-gray-500-hovered color-blue-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date, ['format' => 'DD.MM.YY HH:mm'])}} {{$timezones[$timezone_id]['timezone']}}</span>
						@else
							<span class="color-gray-500-hovered color-blue-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date, ['locale' => 'en', 'format' => 'DD.MM.YY h:mm A'])}} {{$timezones[$timezone_id]['timezone']}}</span>
						@endif
					@else
						<span class="color-gray">-</span>
					@endif
				</p>
				
				<p class="pointer" title="Кликните для копирования">
					<strong class="w3rem d-inline-block text-end">мск</strong>:
					@if($date)
						<span class="color-gray-500-hovered color-blue-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date_msc, ['locale' => 'en', 'format' => 'DD.MM.YY HH:mm'])}} МСК</span>
					@else
						<span class="color-gray">-</span>
					@endif
				</p>
			</div>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block order__ordertype">
			<p>{{$order_type_title ?? null}}</p>
		</div>
	</div>
	<div class="col">
		<div class="order__block">
			<x-scrollblock
				scrollType="light"
				height="46px"
				vertical="center"
				>
				<p class="fz12px lh90 preline breakword">{{$raw_data ?? 'нет данных'}}</p>
			</x-scrollblock>
		</div>
	</div>
	<div class="col-auto">
		<div class="order__block">
			<div class="d-flex justify-content-between align-items-center" ordercommentblock>
				<div class="order__comment scrollblock scrollblock-light" rowcomment>
					@if($last_comment['message'] ?? false)
						<p class="fz10px color-gray mb4px" date>
							{{DdrDateTime::date($last_comment['created_at'] ?? null, ['format' => 'D.MM.YYYY'])}}
							{{DdrDateTime::time($last_comment['created_at'] ?? null)}}
							от
							<span 
								@class([
									'color-green' => $last_comment['user_type'] == 1,
									'color-red' => $last_comment['user_type'] == 2,
								])
								>{{$last_comment['self'] ? 'меня' : (($last_comment['author']['name'] ?? 'удаленный '.($last_comment['user_type'] == 1 ? 'оператор' : 'админ')) ?: ($last_comment['author']['pseudoname'] ?? 'удаленный '.($last_comment['user_type'] == 1 ? 'оператор' : 'админ')))}}</span>
						</p>
						<p class="fz12px lh90">{{$last_comment['message'] ?? '-'}}</p>
					@else
						<p class="fz10px color-gray mb4px" date></p>
						<p class="fz10px color-gray-400">Нет комментариев</p>
					@endif
				</div>
				<div class="align-self-center ml-2px">
					<x-button
						size="verysmall"
						w="2rem-5px"
						variant="gray"
						action="openCommentsWin:{{$id}},{{$order}}"
						title="Открыть комментарии">
						<i class="fa-regular fa-comments"></i>
					</x-button>
				</div>
			</div>		
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
		<div class="order__block{{-- {{$status == \App\Enums\OrderStatus::necro ? ' order__block-noborder' : ''}} --}}">
			{{-- <a target="_blank" href="{{$link ?? '#'}}">{{$link ?? 'Нет ссылки'}}</a> --}}
			<x-button
				class="order__link"
				disabled="{{!$link ?? true}}"
				title="{{$link ? 'Перейти' : 'Нет ссылки'}}"
				size="small"
				w="50px"
				variant="purple"
				action="openLink:{{$link ?? null}}"
				>
				<i class="fa-solid fa-fw fa-link"></i>
			</x-button>
		</div>
	</div>
	
	<div class="col-auto">
		<div class="order__block w3rem-8px">
			@if($is_doprun ?? false)
				<div class="d-inline-flex align-items-center justify-content-center">
					<div class="w2rem h2rem border-rounded-circle" style="background-color: {{$doprunStatus['color'] ?? null}};" title="{{$doprunStatus['name'] ?? null}}"></div>
					{{-- <p class="fz12px ml5px" rowstatustext="">{{$doprunStatus['name'] ?? null}}</p> --}}
				</div>
			@endif
		</div>
	</div>
	
	<div class="col-auto">
		<div class="order__block order__block-noborder">
			<x-buttons-group class="order__right" size="small">
				@if(!isset($status) || $status == \App\Enums\OrderStatus::new)
					<x-button w="50px" action="toCancelListBtn:{{$id ?? null}}" variant="red" title="В отмененные"><i class="fa-solid fa-fw fa-ban"></i></x-button>
					<x-button w="50px" action="toWaitListBtn:{{$id ?? null}}" variant="blue" title="В лист ожидания"><i class="fa-solid fa-fw fa-hourglass-half"></i></x-button>
				@elseif($status == \App\Enums\OrderStatus::wait)
					<x-button w="50px" action="toCancelListBtn:{{$id ?? null}}" variant="red" title="В отмененные"><i class="fa-solid fa-fw fa-ban"></i></x-button>
					<x-button w="50px" action="toNecroListBtn:{{$id ?? null}}" variant="dark" title="В некроту"><i class="fa-solid fa-fw fa-skull"></i></x-button>
				@elseif($status == \App\Enums\OrderStatus::cancel)
				@elseif($status == \App\Enums\OrderStatus::necro)
					<x-button w="50px" action="toWaitListBtn:{{$id ?? null}}" variant="blue" title="В лист ожидания"><i class="fa-solid fa-fw fa-hourglass-half"></i></x-button>
				@endif
				
				@if($status != \App\Enums\OrderStatus::necro)
					<x-button w="50px" variant="neutral" action="toTimesheetBtn:{{$id ?? null}},{{$date_msc ?? null}},{{$order ?? '-'}}" title="Привязать заказ к событию"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
				@endif
				@cando('edit-attached-order:site')
					<x-button w="50px" variant="green" action="editOrder:{{$id ?? null}},{{$order ?? '-'}}" title="Редактировать заказ"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
				@endcando
			</x-buttons-group>
		</div>
	</div>
	
</li>