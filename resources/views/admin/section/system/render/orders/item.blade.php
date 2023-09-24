<x-table.tr class="h4rem">
	<x-table.td class="h-center"><strong class="fz12px">{{$loop->index + 1}}</strong></x-table.td>
	@cando('nomer-zakaza-(klient):site')<x-table.td><p class="fz12px"orderordernumber>{{$order}}</p></x-table.td> @endcando
	
	@cando('data-(klient):site')
	<x-table.td>
		<p class="fz12px pointer" title="Кликните для копирования">
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
		
		<p class="fz12px pointer" title="Кликните для копирования">
			<strong class="w3rem d-inline-block text-end">мск</strong>:
			@if($date)
				<span class="color-gray-500-hovered color-blue-active noselect" onclick="$.copyToClipboard(event)">{{DdrDateTime::date($date_msc, ['locale' => 'en', 'format' => 'DD.MM.YY HH:mm'])}} МСК</span>
			@else
				<span class="color-gray">-</span>
			@endif
		</p>
	</x-table.td>
	@endcando
	
	@cando('dannye-(klient):site')
		<x-table.td>
			@if($rawDataHistory ?? false)
				<div class="d-flex justify-content-between align-items-center">
					<div class="mr5px scrollblock scrollblock-light minh-1rem-4px maxh3rem-1px w100">
						<p class="fz12px lh90 preline" orderrawdata>{{$raw_data}}</p>
					</div>
					<div class="align-self-center">
						<i
							class="fa-solid fa-fw fa-pen-to-square fz18px pointer color-green color-green-pointer color-green-active"
							onclick="$.openRawDataHistoryWin(this, {{$id}}, '{{$order}}')"
							orderrawcounter
							title="Изменений: {{$rawDataHistory}}"
							></i>
					</div>
				</div>
			@else
				<p class="fz12px lh90 preline" orderrawdata>{{$raw_data}}</p>
			@endif
		</x-table.td>
	@endcando
	
	@cando('invayt-(klient):site')<x-table.td><p class="fz12px" orderservername>{{$server_name}}</p></x-table.td> @endcando
	
	@cando('kommentariy-(klient):site')
	<x-table.td>
		<div class="d-flex justify-content-between align-items-center" ordercommentblock>
			<div class="mr5px scrollblock scrollblock-light minh-1rem-4px maxh3rem-1px w100" rowcomment>
				@if($last_comment)
					<p class="fz12px lh900 format wodrbreak color-gray-500">{{$last_comment['message'] ?? null}}</p>
				@endif
			</div>
			<div class="align-self-center">
				<x-button
					size="verysmall"
					w="2rem-5px"
					variant="gray"
					action="openCommentsWin:{{$id}},{{$order}}"
					title="Открыть комментарии">
					<i class="fa-regular fa-fw fa-comments"></i>
				</x-button>
			</div>
		</div>
	</x-table.td>
	@endcando
	
	@cando('stoimost-(klient):site')
	<x-table.td class="h-end">
		<p class="fz12px nowrap"><span orderprice>{{$price}}</span> @symbal(dollar)</p>
	</x-table.td>
	@endcando
	
	@cando('dannye-(klient):site')
		<x-table.td class="w-spacer p-0"></x-table.td>
	@else
		<x-table.td class="w-auto p-0"></x-table.td>
	@endcando
	
	@cando('uvedomleniya-(klient):site')
		@if(isset($notifyButtons) && $notifyButtons)
			<x-table.td class="h-center">
				<x-buttons-group size="verysmall" gx="5">
					@foreach($notifyButtons as $button)
						<x-button
							action="slackNotifyAction:{{$button['id'] ?? null}},{{$id}}"
							variant="{{$button['color'] ?? 'neutral'}}"
							title="{{$button['title'] ?? ''}}"
							>
							<i class="fa-solid fa-fw fa-{{$button['icon'] ?? 'check'}}"></i>
						</x-button>
					@endforeach
				</x-buttons-group>
			</x-table.td>
		@endif
	@endcando
	
	
	
	
	@cando('status-(klient):site')
	<x-table.td @class([
			'h-center' => !isset($showType['text']) || !$showType['text']
		])
		>
		@if($confirmed)
			<i class="fa-regular fa-fw fa-clock color-gray fz18px" title="На подтверждении"></i>
		@elseif($confirm)
			<i class="fa-regular fa-fw fa-circle-check color-green fz18px" title="Подтвержден"></i>
		@else
			<div class="d-inline-flex align-items-center pointer" onclick="$.openStatusesTooltip(this, {{$id}}, {{$timesheet_id}}, '{{$status}}')">
				@if($showType['color'] ?? false)
					<div
						class="w2rem h2rem border-rounded-circle"
						style="background-color: {{$statusesSettings[$status]['color'] ?? null}};"
						title="{{$statusesSettings[$status]['name'] ?? null}}"
						rowstatuscolor
						></div>
				@endif
				
				@if($showType['icon'] ?? false)
					<i
						class="fa-solid fa-fw fa-{{$statusesSettings[$status]['icon'] ?? null}}"
						style="color: {{$statusesSettings[$status]['color'] ?? null}};"
						title="{{$statusesSettings[$status]['name'] ?? null}}"
						rowstatusicon
						></i>
				@endif
				
				@if($showType['text'] ?? false)
					<p class="fz12px ml5px" rowstatustext>{{$statusesSettings[$status]['name'] ?? null}}</p>
				@endif
				
				@if(!isset($showType) || empty($showType))
					<p class="fz12px ml5px" rowstatustext>{{$statusesSettings[$status]['name'] ?? null}}</p>
				@endif
			</div>
		@endif
			
	</x-table.td>
	@endcando
	
	@cando('ssylka-(klient):site')
	<x-table.td class="h-center v-center">
		<x-button
			size="verysmall"
			w="2rem-5px"
			variant="purple"
			action="openLink:{{$link}}"
			title="Перейти"
			disabled="{{!$link ?? false}}"
			tag="orderlink"
			>
			<i class="fa-solid fa-fw fa-arrow-up-right-from-square"></i>
		</x-button>
	</x-table.td>
	@endcando
	
	@cando('deystviya-(klient):site')
	<x-table.td class="h-center">
		<x-buttons-group group="verysmall" w="2rem-5px" gx="4" inline>
			{{-- <x-button variant="green" title=""><i class="fa-solid fa-info"></i></x-button> --}}
			<x-button variant="gray" action="detachTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}}" title="Отвязать от события"><i class="fa-solid fa-fw fa-link-slash"></i></x-button>
			<x-button variant="yellow" action="relocateTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}},move" title="Переместить заказ"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
			<x-button variant="yellow" action="relocateTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}},clone" title="Клонировать заказ"><i class="fa-regular fa-fw fa-clone"></i></x-button>
			<x-button variant="blue" action="editTimesheetOrder:{{$id}},{{$order}},{{$timesheet_id}}" title="Редактировать заказ"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
		</x-buttons-group>
	</x-table.td>
	@endcando
</x-table.tr>