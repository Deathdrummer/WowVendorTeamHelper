{{-- <x-data :data="$data"> --}}
@if($list)
	@foreach ($list as $item)
		@if($listType == 'past')
			@include($itemView, $item)
		@elseif($listType == 'actual' && ((($item['isPast'] ?? false) && $showPastOrdersInActual) || !$item['isPast']))
			@include($itemView, $item)
		@endif
	@endforeach
@endif
{{-- </x-data> --}}