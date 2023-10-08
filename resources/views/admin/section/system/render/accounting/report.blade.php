@if ($test)
<x-table class="w100">
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w16rem h-end"><p class="fz18px"><sub>Команды</sub> \ <sup>Периоды</sup></p></x-table.td>
			@foreach($allPeriods as $pId => $pTitle)
				<x-table.td class="w10rem h-center"><strong>{{$pTitle}}</strong></x-table.td>
			@endforeach
			<x-table.td class="w-auto"></x-table.td>
			<x-table.td class="w15rem h-center"><strong>Итого по периодам</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($commands as $cId => $cTitle)
			<x-table.tr class="h3rem">
				<x-table.td class="h-end"><strong>{{$cTitle}}</strong></x-table.td>
				@foreach($allPeriods as $pId => $pTitle)
					<x-table.td class="h-end">
						@isset($test[$cId][$pId])
							<p>{{$test[$cId][$pId]}} @symbal(dollar)</p>
						@else
							<p class="color-gray-400">-</p>
						@endisset
					</x-table.td>
				@endforeach
				<x-table.td class="w-auto"></x-table.td>
				<x-table.td class="h-end"><strong>{{$test[$cId]['all'] ?? '-'}} @symbal(dollar)</strong></x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
	<x-table.foot>
		<x-table.tr class="h5rem">
			<x-table.td class="w16rem h-end"><strong>Итого по командам</strong></x-table.td>
			@foreach($allPeriods as $pId => $pTitle)
				<x-table.td class="w10rem h-end">
					@isset($test['periods'][$pId])
						<strong>{{$test['periods'][$pId]}} @symbal(dollar)</strong>
					@else
						<p class="color-gray-400">-</p>
					@endisset
				</x-table.td>
			@endforeach
			<x-table.td class="w-auto"></x-table.td>
			<x-table.td class="w15rem h-end"><strong>{{$test['total']}} @symbal(dollar)</strong></x-table.td>
		</x-table.tr>
			
	</x-table.foot>
</x-table>
@else
	<p class="color-gray-400 text-center">Нет данных</p>
@endif