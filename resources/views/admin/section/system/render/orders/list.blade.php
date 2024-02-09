@if($list)
	<x-table class="w100" scrolled="300px">
		<x-table.head>
			<x-table.tr class="h3rem">
				@if(($orderColsSettings['show'][-1] ?? false) && (!($isAdmin ?? true)))
					<x-table.td class="w3rem-8px h-center">
						<x-button
							size="verysmall"
							variant="yellow"
							tag="choosealltsdorders"
							><i class="fa-solid fa-fw fa-check-double"></i>
						</x-button>
					</x-table.td>
				@endif
				
				@if(($orderColsSettings['show'][-2] ?? false))
					<x-table.td class="w3rem-4px h-center"><strong>№</strong></x-table.td>
				@endif
				
				@forelse($orderColums as ['key' => $column, 'value' => $colKey, 'desc' => $colName])
					@if(Auth::guard('site')->user()->can($column.'-(client):site') && ($orderColsSettings['show'][$colKey] ?? false))
						<x-table.td
							style="width:{{ddrIf([($orderColsSettings['width'][$colKey] ?? '').'px' => ($orderColsSettings['width'][$colKey] ?? false) && !in_array($column, ['data', 'notifies']), (31 * count($notifyButtons ?? 1) + 16).'px' => $column == 'notifies' && isset($notifyButtons), '150px' => !in_array($column, ['data'])])}};"
							@class([
								/*'w'.($orderColsSettings[$colKey]['width'] ?? '') => ($orderColsSettings[$colKey]['width'] ?? false) && !in_array($column, ['data', 'status']),*/
								'w-auto' => $column == 'data',
								'pointer color-neutral-hovered' => in_array($column, ['order', 'invite']),
								'h-end' => in_array($column, ['price']),
								'h-center' => in_array($column, ['notifies']),
							])
							onclick="{{ddrIf(['$.copyOrdersColumn(this)' => $column == 'order', '$.copyInviteColumn(this)' => $column == 'invite'], 'return false');}}"
							title="{{$colName ?? null}}"
							>
							
							@if($column == 'comment')
								<div class="row align-items-center">
									<div class="col">
										<strong>{{$colName ?? '-'}}</strong>
									</div>
									<div class="col-auto">
										<x-button
											variant="darkgray"
											size="verysmall"
											action="openCommentsWin:null,null,{{$timesheetId}}"
											title="Отправить комментарий"
											choosetsbuttons
											hidden
											><i class="fa-regular fa-fw fa-comments"></i>
										</x-button>
									</div>
								</div>
							@elseif($column == 'notifies')
								@if(isset($notifyButtons) && $notifyButtons)
									@if(count($notifyButtons ?? 1) == 3)
										<strong title="Уведомления">Уведомл.</strong>
									@elseif(count($notifyButtons ?? 1) > 3)
										<strong>Уведомления</strong>
									@else
										<i class="fa-brands fa-fw fa-slack" title="Уведомления в Слак"></i>
									@endif
								@endif
							@else
								<strong>{{$colName ?? '-'}}</strong>
							@endif
							
						</x-table.td>
					@endif
				@empty
					<x-table.td class="w-auto"></x-table.td>
				@endforelse
				
				
				@if(Auth::guard('site')->user()->can('data-(client):site') && ($orderColsSettings['show'][4] ?? false))
					<x-table.td class="w-spacer p-0"></x-table.td>
				@else
					<x-table.td class="w-auto p-0"></x-table.td>
				@endif
				
				
				@if(Auth::guard('site')->user()->can('status-(client):site') && ($orderColsSettings['show'][-3] ?? false))
					<x-table.td
						@class([
							'w-10rem' => $showType['text'] ?: false,
							'w-5rem' => (($showType['color'] || $showType['icon']) ?: false),
						])
					 	>
				 		<strong choosetslabel>{{$showType['text'] ? 'Статус' : 'Стат'}}</strong>
							<x-button
								variant="green"
								group="verysmall"
								choosetsbuttons
								hidden
								action="openStatusesTooltip:null,{{$timesheetId}}"
								title="Изменить статус выбранных заказов"
								><i class="fa-solid fa-fw fa-circle-half-stroke"></i></i>
							</x-button>
					 	</x-table.td>
				@endif
				
				@if(Auth::guard('site')->user()->can('link-(client):site') && ($orderColsSettings['show'][-4] ?? false))
					<x-table.td class="w4rem h-center"><i class="fa-solid fa-fw fa-link"></i></x-table.td>
				@endif
				
				
				@if(Auth::guard('site')->user()->can('deystviya-(klient):site') && ($orderColsSettings['show'][-5] ?? false))
					<x-table.td class="w-14rem pl9px">
						<strong choosetslabel>Действия</strong>
						<x-buttons-group group="verysmall" w="2rem-5px" gx="4" inline choosetsbuttons hidden>
							<x-button variant="gray" action="detachTimesheetOrder:null,{{$timesheetId}}" title="Отвязать от события"><i class="fa-solid fa-fw fa-link-slash"></i></x-button>
							<x-button variant="yellow" action="relocateTimesheetOrder:null,{{$timesheetId}},null,move" title="Переместить выбранные заказы"><i class="fa-solid fa-fw fa-angles-right"></i></x-button>
							<x-button variant="yellow" action="relocateTimesheetOrder:null,{{$timesheetId}},null,clone" title="Клонировать выбранные заказы"><i class="fa-regular fa-fw fa-clone"></i></x-button>
						</x-buttons-group>
					</x-table.td>
				@endif
			</x-table.tr>
		</x-table.head>
		<x-table.body>
			@foreach ($list as $item)
				@include($itemView, $item)
			@endforeach
		</x-table.body>
	</x-table>
@else
	<p class="color-gray-400 text-center mt2rem fz14px">Нет заказов</p>
@endif