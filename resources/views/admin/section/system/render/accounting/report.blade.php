@if ($map)
<x-table class="w100">
	<x-table.head>
		<x-table.tr class="h4rem">
			<x-table.td class="w16rem h-end"><p class="fz18px"><sub>Команды</sub> \ <sup>Периоды</sup></p></x-table.td>
			@foreach($periods as $pId => $pTitle)
				<x-table.td class="w10rem h-center"><strong>{{$pTitle}}</strong></x-table.td>
			@endforeach
			<x-table.td class="w-auto"></x-table.td>
			<x-table.td class="w10rem h-center"><strong>Итого</strong></x-table.td>
		</x-table.tr>
	</x-table.head>
	<x-table.body>
		@foreach($commands as $cId => $cTitle)
			<x-table.tr class="h3rem">
				<x-table.td class="h-end"><strong>{{$cTitle}}</strong></x-table.td>
				@foreach($periods as $pId => $pTitle)
					<x-table.td class="h-center">
						@isset($map[$cId][$pId])
							<p>{{$map[$cId][$pId]}} @symbal(dollar)</p>
						@else
							<p class="color-gray-400">-</p>
						@endisset
					</x-table.td>
				@endforeach
				<x-table.td class="w-auto"></x-table.td>
				<x-table.td class="h-center"><strong>{{$map[$cId]['all']}} @symbal(dollar)</strong></x-table.td>
			</x-table.tr>
		@endforeach
	</x-table.body>
	<x-table.foot>
		<x-table.tr class="h5rem">
			<x-table.td class="w16rem h-end"><strong>Итого по периодам</strong></x-table.td>
			@foreach($periods as $pId => $pTitle)
				<x-table.td class="w10rem h-center">
					@isset($map['periods'][$pId])
						<strong>{{$map['periods'][$pId]}} @symbal(dollar)</strong>
					@else
						<p class="color-gray-400">-</p>
					@endisset
				</x-table.td>
			@endforeach
			<x-table.td class="w-auto"></x-table.td>
			<x-table.td class="w10rem h-center"><strong>{{$map['total']}} @symbal(dollar)</strong></x-table.td>
		</x-table.tr>
			
	</x-table.foot>
</x-table>
@else
	<p class="color-gray-400 text-center">Нет данных</p>
@endif