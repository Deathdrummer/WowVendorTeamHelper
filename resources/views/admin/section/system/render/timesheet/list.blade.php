{{-- <x-data :data="$data"> --}}
	@if($list)
		@foreach ($list as $item)
			@setting('show_past_orders_in_actual')
				@include($itemView, $item)
			@endsetting
		@endforeach
	@endif
{{-- </x-data> --}}