{{-- <x-data :data="$data"> --}}
@if($list)
	@foreach ($list as $item)
		@if($item['isPast'] ?? false)
			@setting('show_past_orders_in_actual')
				@include($itemView, $item)
			@endsetting
		@endif
	@endforeach
@endif
{{-- </x-data> --}}