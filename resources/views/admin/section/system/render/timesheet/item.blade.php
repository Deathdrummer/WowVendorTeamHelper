<x-table.tr class="h4rem pointer noselect bg-light-hovered" onclick="$.timesheetGetOrders(this, {{$id}})">
	<x-table.td>
		<p>{{DdrDateTime::date($datetime)}}</p>
	</x-table.td>
	<x-table.td>
		<p>
			<span
				 class="color-gray-500-hovered color-blue-active noselect"
				 onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM'])}} {{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}')"
				 >{{DdrDateTime::time($datetime, ['format' => ['en' => 'h:i a']])}}</span>
			/
			@if(isset($data['commands'][$command_id]['format_24']) && $data['commands'][$command_id]['format_24'])
				<span
					class="color-gray-500-hovered color-blue-active noselect"
					onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM', 'shift' => $data['commands'][$command_id]['shift']])}} {{DdrDateTime::time($datetime, ['format' => 'H:i', 'shift' => $data['commands'][$command_id]['shift']])}} {{$data['commands'][$command_id]['timezone']}}')"
					>{{DdrDateTime::time($datetime, ['format' => 'H:i', 'shift' => $data['commands'][$command_id]['shift']])}} <span class="color-gray">{{$data['commands'][$command_id]['timezone']}}</span></span>
			@else
				<span
					class="color-gray-500-hovered color-blue-active noselect"
					onclick="$.copyToClipboard(event, '{{DdrDateTime::date($datetime, ['format' => 'DD.MM', 'shift' => $data['commands'][$command_id]['shift']])}} {{DdrDateTime::time($datetime, ['locale' => 'en', 'format' => 'h:i A', 'shift' => $data['commands'][$command_id]['shift']])}} {{$data['commands'][$command_id]['timezone']}}')"
					>{{DdrDateTime::time($datetime, ['locale' => 'en', 'format' => 'h:i A', 'shift' => $data['commands'][$command_id]['shift']])}} <span class="color-gray">{{$data['commands'][$command_id]['timezone']}}</span></span>
			@endif
		</p>
	</x-table.td>
	<x-table.td>
		<p>{{$data['commands'][$command_id]['title'] ?? '-'}}</p>
	</x-table.td>
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-show-price:site')))
		<x-table.td class="h-center">
			@if($orders_sum_price)
				<p orderssum>{{$orders_sum_price}} @symbal(dollar)</p>
			@else
				<p class="color-gray-600">-</p>
			@endif
		</x-table.td>
	@endif
	<x-table.td>
		<div class="d-flex justify-content-between align-items-center" ordercommentblock>
			<div class="mr5px scrollblock scrollblock-light minh-1rem-4px maxh3rem-1px w100" rowcomment>
				@if($comment)
					<p class="fz12px lh900 format wodrbreak color-gray-500">{{$comment ?? null}}</p>
				@endif
			</div>
			<div class="align-self-center">
				<x-button
					size="verysmall"
					w="2rem-5px"
					variant="gray"
					action="openTimesheetCommentWin:{{$id}}"
					title="Открыть комментарии">
					<i class="fa-regular fa-comments"></i>
				</x-button>
			</div>
		</div>
		
	</x-table.td>
	<x-table.td>
		<p>{{$data['events_types'][$event_type_id] ?? '-'}}</p>
	</x-table.td>
	<x-table.td class="h-center">
		<p orderscount>{{$orders_count}}</p>
	</x-table.td>
	<x-table.td class="h-center" timesheetrulesblock>
		<x-buttons-group group="small" w="3rem" gx="5">
			<x-button variant="green" action="timesheetNewOrder:{{$id}}" title="Добавить заказ"><i class="fa-solid fa-fw fa-plus"></i></x-button>
			<x-button variant="blue" action="timesheetEdit:{{$id}}" title="Изменить"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
			<x-button variant="red" action="timesheetRemove:{{$id}}" title="Удалить"><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
		</x-buttons-group>
	</x-table.td>
</x-table.tr>