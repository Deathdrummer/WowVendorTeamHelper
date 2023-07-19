@aware([
	'variant'	=> 'gray',
	'px'		=> 10,
	'py'		=> 0,
	'many'  	=> null, // множественный выбор
])

@props([
    'action'	=> null,
    'variant'	=> $variant,
    'active'	=> null
])



<div class="col-auto chooser__col">
	<div
		{{$attributes->class([
			'chooser__item',
			'chooser__item_many' => $many,
			'chooser_color-'.$variant,
			'chooser__item_active' => $active,
			'pl'.$px.'px' => $px,
			'pr'.$px.'px' => $px,
			'pt'.$py.'px' => $py,
			'pb'.$py.'px' => $py,
		])}}
		chooseritem
		onclick="$.{{getActionFuncName($action)}}(this, this.classList.contains('chooser__item_active'){{buildActionParams($action)}})"
		{{-- {{$attributes->filter(fn ($value, $key) => $key !== 'class')}} --}}
		>
		<span>{{$slot}}</span>
	</div>     
</div>