@props([
	'id'            => 'scrollBlock'.rand(0,9999999),
	'scrollType'    => null,
	'height'		=> 'inherit',
	'vertical'		=> 'start',
])

<div
	{{$attributes->class([
		'scrollblock',
		'scrollblock-'.$scrollType => $scrollType,
	])}}
	style="height: {{$height}};">
	<div
		style="min-height: {{$height}};"
		@class([
			'd-flex',
			'align-items-'.$vertical,
		])
	>{{$slot}}</div>
</div>