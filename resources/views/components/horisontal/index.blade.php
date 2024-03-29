@props([
	'id'				=> 'horisontal'.rand(0,9999999),
	'speed'				=> 100,
	'step'				=> 100,
	'scroll'			=> true,
	'track'				=> false,
	'ignore'			=> '[noscroll]',
	'addict'			=> false,
	'hidescroll'		=> false,
	'movekey'			=> null,
	'ignoremovekeys'	=> null,
	'classwhenmoved'	=> null,
])


<div
	{{$attributes->class([
		'horisontal',
		'horisontal_track-hide' => !$track || $track == 'false',
		'horisontal_track-'.$track => $track,
	])}}
	id="{{$id}}"
	>
	<div @class([
			'horisontal__track',
		])>{{$slot}}</div>
</div>

<script type="module">
	let selector = '#{{$id}}',
		scroll = {{$scroll}},
		ignoreSelectors = '{{$ignore}}',
		addict = '{{$addict}}',
		step = '{{$step}}',
		speed = '{{$speed}}',
		moveKey = '{{$movekey}}',
		ignoreMoveKeys = '{{$ignoremovekeys}}',
		classWhenMoved = '{{$classWhenMoved}}';
	
	
	//Горизонтальная прокрутка блока мышью и колесиком
	//	- шаг прокрутки (для колеса)
	//	- скорость прокрутки (для колеса)
	//	- разрешить прокрутку колесом
	//	- Игнорировать селекторы
	//	- добавить блок к синхронному скроллу
	$(selector).ddrScrollX({
		scrollStep: step,
		scrollSpeed: speed,
		enableMouseScroll: scroll,
		ignoreSelectors,
		addict,
		moveKey,
		ignoreMoveKeys,
		classWhenMoved,
	});
</script>