@aware([
	'space'		=> '0px',
	'stretch'	=> 1,
])

<div
	{{$attributes->class([
		'horisontal__item',
		'horisontal__item_stretch' => $stretch === 1,
		'ml'.$space => $space
	])}}
	>{{$slot}}</div>