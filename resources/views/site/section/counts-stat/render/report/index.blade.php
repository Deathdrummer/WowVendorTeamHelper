<x-table class="w100" scrolled="70vh">
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w10rem">
				
			</x-table.td>
			<x-table.td class="w20rem">
				<strong>{{$periodTitle}}</strong>
			</x-table.td>
			@foreach($commands as $cId => $cTitle)
				<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$cTitle}}</strong></x-table.td>
			@endforeach
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($buildData as $day => $ordersTypesData) {{-- день -> тип заказа -> команда --}}
			@foreach($ordersTypesData as $orderTypeId => $commandsCounts)
				<x-table.tr>
					<x-table.td>{{$day}}</x-table.td>
					<x-table.td>{{$ordersTypes[$orderTypeId] ?? '-'}}</x-table.td>
					@foreach($commands as $cId => $cTitle)
						<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$commandsCounts[$cId] ?? '-'}}</strong></x-table.td>
					@endforeach
					{{-- @foreach($commands as $commandId => $counts)
						
							@foreach($counts as $orderType => $count)
								<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$cTitle}}</strong></x-table.td>
							@endforeach
						
					@endforeach --}}
				</x-table.tr>
			@endforeach
		@endforeach
	</x-table.body>
</x-table>