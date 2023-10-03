<x-table class="w100" scrolled="calc(100vh - 145px)">
	<x-table.head>
		<x-table.tr class="h4rem clear">
			<x-table.td class="w10rem"></x-table.td>
			<x-table.td class="w20rem"><strong>{{$periodTitle}}</strong></x-table.td>
			<x-table.td class="w-auto p-0">
				<x-table class="w100" scrolled="false" noborder>
					<x-table.body>
						<x-table.tr class="h4rem clear" noborder>
							@foreach($map['commands'] as $cId => $cData)
								<x-table.td class="w7rem h-center" style="background-color:{{$cData['color']}};">
									<p class="fz12px text-center text-shadow" invert-color>{{$cData['title']}}</p>
								</x-table.td>
							@endforeach
							@foreach($map['regions'] as $rId => $rTitle)
								<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$rTitle}}</strong></x-table.td>
							@endforeach
							<x-table.td class="w7rem h-center"><strong class="fz12px text-center">ALL</strong></x-table.td>
							<x-table.td class="w-spacer"></x-table.td>
						</x-table.tr>
					</x-table.body>
				</x-table>
			</x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($buildData as $day => $ordersTypesData)
			@if($day == 'all')
				<x-table.tr class="clear h2rem">
					<x-table.td></x-table.td>
				</x-table.tr>
			@endif
			<x-table.tr class="clear">
				<x-table.td class="v-start">
					<p class="mt9px">{!!$day == 'all' ? '<strong>Итог</strong>' : $day!!}</p>
				</x-table.td>
				<x-table.td class="p-0">
					<x-table class="w100" scrolled="false" noborder>
						<x-table.body>
							@foreach($ordersTypesData as $orderTypeId => $commandsCounts)
								<x-table.tr class="h4rem clear">
									<x-table.td class="w100 h-start" style="background-color: {{$ordersTypes[$orderTypeId]['color'] ?? null}};" noborder>
										<strong class="fz12px text-center" invert-color>{{$ordersTypes[$orderTypeId]['title'] ?? '-'}}</strong>
									</x-table.td>
								</x-table.tr>
							@endforeach
						</x-table.body>
					</x-table>
				</x-table.td>
				<x-table.td class="p-0 w-auto">
					<x-table class="w100" scrolled="false" noborder>
						<x-table.body>
							@foreach($ordersTypesData as $orderTypeId => $commandsCounts)
								<x-table.tr class="h4rem clear">
									@foreach($map['commands'] as $cId => $cData)
										<x-table.td class="w7rem h-center" style="background-color: {{$day != 'all' ? ($cData['color'] ?? null) : '#ffefb7'}}">
											<p class="fz12px text-center" invert-color>{{$commandsCounts['commands'][$cId] ?? '-'}}</p>
										</x-table.td>
									@endforeach
									@foreach($map['regions'] as $rId => $rTitle)
										<x-table.td class="w7rem h-center" style="background-color: {{$day == 'all' ? '#ffefb7' : ''}}">
											<strong class="fz12px text-center">{{$commandsCounts['regions'][$rId] ?? '-'}}</strong>
										</x-table.td>
									@endforeach
									<x-table.td class="w7rem h-center" style="background-color: {{$day == 'all' ? '#ffefb7' : ''}}">
										<strong class="fz12px text-center">{{$commandsCounts['all'] ?? '-'}}</strong>
									</x-table.td>
									<x-table.td class="w-spacer"></x-table.td>
								</x-table.tr>
							@endforeach
						</x-table.body>
					</x-table>
				</x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
</x-table>