<x-table class="w100" scrolled="calc(100vh - 145px)">
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w10rem"></x-table.td>
			<x-table.td class="w20rem"><strong>{{$periodTitle}}</strong></x-table.td>
			<x-table.td class="w-auto p-0">
				<x-table class="w100" noborder>
					<x-table.body>
						<x-table.tr class="h4rem" noborder>
							@foreach($commands as $cId => $cTitle)
								<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$cTitle}}</strong></x-table.td>
							@endforeach
						</x-table.tr>
					</x-table.body>
				</x-table>
			</x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($buildData as $day => $ordersTypesData)
			<x-table.tr>
				<x-table.td class="v-start">
					<p class="mt13px">{{$day}}</p>
				</x-table.td>
				<x-table.td class="p-0">
					<x-table class="w100" noborder>
						<x-table.body>
							@foreach($ordersTypesData as $orderTypeId => $commandsCounts)
								<x-table.tr class="h4rem" noborder>
									<x-table.td class="w7rem h-center" noborder><strong class="fz12px text-center">{{$ordersTypes[$orderTypeId] ?? '-'}}</strong></x-table.td>
								</x-table.tr>
							@endforeach
						</x-table.body>
					</x-table>
				</x-table.td>
				<x-table.td class="p-0 w-auto">
					<x-table class="w100" noborder>
						<x-table.body>
							@foreach($ordersTypesData as $orderTypeId => $commandsCounts)
								<x-table.tr class="h4rem">
									@foreach($commands as $cId => $cTitle)
										<x-table.td class="w7rem h-center"><strong class="fz12px text-center">{{$commandsCounts[$cId] ?? '-'}}</strong></x-table.td>
									@endforeach
								</x-table.tr>
							@endforeach
						</x-table.body>
					</x-table>
				</x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
</x-table>