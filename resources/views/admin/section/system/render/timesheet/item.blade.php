<x-table.tr class="h4rem pointer noselect bg-light-hovered{{$isPast ? ' fulfilled' : ''}}" onclick="$.timesheetGetOrders(this, {{$id}})" tsevent="{{$id}}">
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-date:site')))
		<x-table.td>
			<p>{{DdrDateTime::date($datetime)}}</p>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-time:site')))
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
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-command:site')))
		<x-table.td style="background-color: {{$commandsColors[$command_id] ?? ''}};">
			<p class="inverse ignore">{{$data['commands'][$command_id]['title'] ?? '-'}}</p>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-stat:site')))
		<x-table.td>
			<div class="row gx-4">
				@forelse($orders_types_stat as $orderType => $count)
					<div class="col-auto">
						<div class="minw3rem h3rem p2px border-all border-gray-70 border-radius-3px bg-white text-center">
							<p class="fz12px lh100"><strong>{{$orderType ?? '-'}}</strong></p>
							<p class="fz12px lh100">{{$count}}</p>
						</div>
					</div>
				@empty
					<span>-</span>
				@endforelse
			</div>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-show-price:site')))
		<x-table.td class="h-center">
			@if($orders_sum_price)
				<p orderssum>{{$orders_sum_price}} @symbal(dollar)</p>
			@else
				<p class="color-gray-600">-</p>
			@endif
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-type:site')))
		<x-table.td>
			<p>{{$data['events_types'][$event_type_id] ?? '-'}}</p>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-comment:site')))
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
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('screenstat:site')))
		<x-table.td class="h-center">
			<x-button
				size="verysmall"
				w="2rem-5px"
				variant="orange"
				action="timesheetScreenStat:{{$id}}"
				title="Отправить скриншот со статистикой"
				><i class="fa-solid fa-fw fa-plus"></i></x-button>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-orders-count:site')))
		<x-table.td class="h-center">
			<p orderscount>{{$orders_count}}</p>
		</x-table.td>
	@endif
	
	@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('events-actions:site')))
		<x-table.td class="h-center" timesheetrulesblock>
			<x-buttons-group group="small" w="3rem" gx="5">
				@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('timesheet-new-order-button:site')))
					<x-button variant="green" action="timesheetNewOrder:{{$id}}" title="Добавить заказ"><i class="fa-solid fa-fw fa-plus"></i></x-button>
				@endif
				
				@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('timesheet-edit-button:site')))
					<x-button variant="blue" action="timesheetEdit:{{$id}}" title="Изменить"><i class="fa-solid fa-fw fa-pen-to-square"></i></x-button>
				@endif
				
				@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('timesheet-remove-button:site')))
					<x-button variant="red" action="timesheetRemove:{{$id}}" title="Удалить"><i class="fa-solid fa-fw fa-trash-can"></i></x-button>
				@endif
			</x-buttons-group>
		</x-table.td>
	@endif
</x-table.tr>