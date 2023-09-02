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
	
	@cando('dannye-(klient):site')<x-table.td><p class="fz12px lh90 preline" orderrawdata>{{$raw_data}}</p></x-table.td> @endcando
	
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
					<i class="fa-regular fa-comments"></i>
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
	
	<x-table.td>
		<p>
			<span
				 class="color-gray-500-hovered color-blue-active noselect"
				 onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM'])}} {{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}')"
				 >{{DdrDateTime::date($datetime, ['format' => 'DD.MM.YYYY'])}} {{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}</span>
			/
			@if(isset($commands[$command_id]['format_24']) && $commands[$command_id]['format_24'])
				<span
					class="color-gray-500-hovered color-blue-active noselect"
					onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM'])}} {{DdrDateTime::time($datetime, ['format' => 'H:i', 'shift' => $commands[$command_id]['shift']])}} {{$commands[$command_id]['timezone']}}')"
					>{{DdrDateTime::time($datetime, ['format' => 'H:i', 'shift' => $commands[$command_id]['shift']])}} <span class="color-gray">{{$commands[$command_id]['timezone']}}</span></span>
			@else
				<span
					class="color-gray-500-hovered color-blue-active noselect"
					onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM'])}} {{DdrDateTime::time($datetime, ['locale' => 'en', 'format' => 'h:i A', 'shift' => $commands[$command_id]['shift']])}} {{$commands[$command_id]['timezone']}}')"
					>{{DdrDateTime::time($datetime, ['locale' => 'en', 'format' => 'h:i A', 'shift' => $commands[$command_id]['shift']])}} <span class="color-gray">{{$commands[$command_id]['timezone']}}</span></span>
			@endif
		</p>
	</x-table.td>
	
	<x-table.td><p>{{$commands[$command_id]['title'] ?? '-'}}</p></x-table.td>
	
	@if(auth('site')->user()->can('dannye-(klient):site') && $type == 'actual')
		<x-table.td class="w-spacer p-0"></x-table.td>
	@elseif($type == 'actual')
		<x-table.td class="w-auto p-0"></x-table.td>
	@endif
	
	@if(auth('site')->user()->can('deystviya-(klient):site') && $type == 'actual')
		<x-table.td class="h-center">
			<x-buttons-group
				group="verysmall"
				w="2rem-5px"
				gx="4"
				inline
				>
				<x-button
					action="confirmOrderAction:{{$id}}"
					variant="blue"
					title="Подтвердить заказ"
					>
					<i class="fa-solid fa-fw fa-check"></i>
				</x-button>
				<x-button
					variant="red"
					action="removeConfirmedOrder:{{$id}},{{$timesheet_id}},{{$order}}"
					title="Удалить заказ из подтвежденных"
					><i class="fa-solid fa-fw fa-trash"></i>
				</x-button>
			</x-buttons-group>
		</x-table.td>
	@endif
	
</x-table.tr>