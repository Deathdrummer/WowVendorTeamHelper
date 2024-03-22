<x-table.tr class="h4rem" tsorder="{{$id}}" fraction="{{$fraction ?? '-'}}">
	@if(($orderColsSettings['show'][-1] ?? false) && (!($isAdmin ?? true)))
		<x-table.td class="h-center">
			<x-checkbox
				size="small"
				tag="choosetsdorder:{{$id}}|{{$status}}"
				/>
		</x-table.td>
	@endif
	
	@if(($orderColsSettings['show'][-2] ?? false))
		<x-table.td class="h-center"><strong class="fz12px">{{$loop->index + 1}}</strong></x-table.td>
	@endif
	
	
	
	@forelse($orderColums as ['key' => $column, 'value' => $colKey, 'desc' => $colName])
		@if(Auth::guard('site')->user()->can($column.'-(client):site') && ($orderColsSettings['show'][$colKey] ?? false))
			<x-table.td
				@class([
					'h-center' => in_array($column, ['notifies']),
					'h-end' => in_array($column, ['type', 'price']),
				])
				>
				@if($column == 'order')
					<p
						class="fz12px color-gray-500-hovered color-blue-active noselect pointer"
						orderordernumber
						onclick="$.copyToClipboard(event, '{{$order}}')"
						title="Кликните для копирования"
						>{{$order}}</p>
				@elseif($column == 'date')
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
				@elseif($column == 'date_add')	
					<p class="fz12px">{{DdrDateTime::date($date_add, ['locale' => 'en', 'format' => 'DD.MM.YY HH:mm', 'shift' => '+'])}} МСК</p>
				@elseif($column == 'type')
					<p class="fz12px preline" ordertype>{{$order_type_title ?? '-'}}</p>
				@elseif($column == 'fraction')
					<p class="fz12px">{{$fraction ?? '-'}}</p>
				@elseif($column == 'battle_tag')
					@if($battle_tag ?? false)
						<p class="fz12px" onclick="$.copyToClipboard(event)">{{$battle_tag}}</p>
					@else
						<p class="fz12px color-gray">-</p>
					@endif
				@elseif($column == 'data')
					<div class="d-flex justify-content-between align-items-center">
						<div class="scrollblock scrollblock-light minh-1rem-4px maxh3rem-1px w100">
							<p class="fz12px lh90 preline breakword" orderrawdata enablecontextmenu>{{$raw_data}}</p>
						</div>
						<div
							class="align-self-center ml5px"
							orderrawhistory
							@if(!($rawDataHistory ?? false)) hidden @endif
							>
							<i
								class="fa-solid fa-fw fa-pen-to-square fz18px pointer color-green color-green-pointer color-green-active"
								onclick="$.openRawDataHistoryWin(this, {{$id}}, '{{$order}}')"
								orderrawcounter
								title="Изменений: {{$rawDataHistory}}"
								></i>
						</div>
					</div>
				@elseif($column == 'invite')
					<p class="fz12px" orderservername>{{$server_name}}</p>
				@elseif($column == 'comment')
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
				@elseif($column == 'price')
					<p class="fz12px nowrap"><span orderprice>{{$price}}</span> @symbal(dollar)</p>
				@elseif($column == 'notifies')
					@if(isset($notifyButtons) && $notifyButtons)
						<x-buttons-group size="verysmall" gx="5">
							@foreach($notifyButtons as $button)
								<x-button
									action="slackNotifyAction:{{$button['id'] ?? null}},{{$id ?? null}},{{$timesheetId ?? null}}"
									variant="{{$button['color'] ?? 'neutral'}}"
									disabled="{{$is_hash_order ?? false}}"
									enabled="{{getGuard() == 'admin' || !isset($button['permission']) || (getGuard() == 'site' && auth('site')->user()->can($button['permission']))}}"
									title="{{$button['title'] ?? ''}}"
									>
									<i class="fa-solid fa-fw fa-{{$button['icon'] ?? 'check'}}"></i>
								</x-button>
							@endforeach
						</x-buttons-group>
					@endif
				@endif
			</x-table.td>
		@endif
	@empty
		<x-table.td class="w-auto"></x-table.td>
	@endforelse
	
	
	
	
	
	@cando('data-(klient):site')
		<x-table.td class="w-spacer p-0"></x-table.td>
	@else
		<x-table.td class="w-auto p-0"></x-table.td>
	@endcando
	
	
	@if(Auth::guard('site')->user()->can('status-(client):site') && ($orderColsSettings['show'][-3] ?? false))
	<x-table.td
		@class([
			'h-center' => !isset($showType['text']) || !$showType['text']
		])
		>
		@if($confirmed)
			<i class="fa-regular fa-fw fa-clock color-gray fz18px" title="На подтверждении"></i>
		@elseif($confirm)
			<i class="fa-regular fa-fw fa-circle-check color-green fz18px" title="Подтвержден"></i>
		@else
			<div
				@class([
					'd-inline-flex',
					'align-items-center',
					'pointer' => $canAnySetStat,
				])
				orderstatusblock="{{$id}}"
				@if($canAnySetStat)onclick="$.openStatusesTooltip(this, {{$id}}, {{$timesheet_id}}, '{{$status}}')" @endif
				>
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
	@endif
	
	@if(Auth::guard('site')->user()->can('link-(client):site') && ($orderColsSettings['show'][-4] ?? false))
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
	@endif
	
	@if(Auth::guard('site')->user()->can('deystviya-(klient):site') && ($orderColsSettings['show'][-5] ?? false))
	<x-table.td class="h-center">
		<x-buttons-group group="verysmall" w="2rem-5px" gx="4" inline>
			{{-- <x-button variant="green" title=""><i class="fa-solid fa-info"></i></x-button> --}}
			<x-button variant="gray" action="detachTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}}" title="Отвязать от события" disabled="{{$status == 'doprun'}}"><i class="fa-solid fa-fw fa-link-slash"></i></x-button>
			<x-button variant="yellow" action="relocateTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}},move" title="Переместить заказ" disabled="{{$status == 'doprun'}}"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
			<x-button variant="yellow" action="relocateTimesheetOrder:{{$id}},{{$timesheet_id}},{{$order}},clone" title="Клонировать заказ"><i class="fa-regular fa-fw fa-clone"></i></x-button>
			<x-button variant="blue" action="editTimesheetOrder:{{$id}},{{$order}},{{$timesheet_id}}" title="Редактировать заказ"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
		</x-buttons-group>
	</x-table.td>
	@endif
</x-table.tr>