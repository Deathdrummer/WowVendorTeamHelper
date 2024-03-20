{{-- <x-data :data="$data"> --}}
@if($list)
	@foreach ($list as $item)
		@if((($item['isPast'] ?? false) && $showPastOrdersInActual) || !$item['isPast'])
			@include($itemView, $item)
		@endif
	@endforeach
@endif
{{-- </x-data> --}}