@props([
	'id'			=> 'card'.rand(0,9999999),
	'loading' 		=> null,
	'ready' 		=> null,
	'title' 		=> null,
	'desc' 			=> null,
	'button'		=> null,
	'buttonId'		=> null,
	'buttonVariant'	=> 'green',
	'buttonSize'	=> 'normal',
	'disableBtn'	=> null,
	'action'		=> null,
	'cando'			=> null,
	'scrolled'		=> null,
])


<div
	{{$attributes->class([
		'card',
		'scrolled' => $scrolled
	])}}
	@if($id)id="{{$id}}"@endif
	>
	@isset($title)
	<div class="card__header">
		<div class="mr20px">
			<h3 class="card__title color-dark">{{$title}}</h3>
			<p class="card__desc color-gray">{{$desc}}</p>
		</div>
		
		@if($button)
			@if($cando)
				@cando($cando)
					<x-button
						id="{{$buttonId}}"
						variant="{{$buttonVariant}}"
						group="{{$buttonSize}}"
						px="10"
						action="{{$action}}"
						disabled="{{isset($disableBtn)}}"
						tag="cardbutton"
						>{{$button}}</x-button>
				@endcando
			@else
				<x-button
					id="{{$buttonId}}"
					variant="{{$buttonVariant}}"
					group="{{$buttonSize}}"
					px="10"
					action="{{$action}}"
					disabled="{{isset($disableBtn)}}"
					tag="cardbutton"
					>{{$button}}</x-button>
			@endif
		@endif
	</div>
	@endisset
	
	@if($scrolled)
		<div class="card__scroll scrollblock" style="max-height: {{$scrolled}};">
			{{$slot}}
		</div>
	@else
		{{$slot}}
	@endif
	
	@isset($loading)
	<div class="card__wait" cardwait>
		<div class="cardwait">
			<img src="{{Vite::asset('resources/images/loading.gif')}}" class="cardwait__icon">
			@if(is_string($loading))
				<p class="cardwait__text">{{$loading}}</p>
			@endif
		</div>
	</div>
	@endisset
</div>




<script type="module">
	let id = '{{$id ?? null}}',
		ready = '{{$ready ?? null}}';
	
	if (ready) {
		$('#'+id).ready(function() {
			$(this).card('ready');
		});
	}
</script>