@props([
	'id'    	=> 'chooser'.rand(0,9999999),
	'many'  	=> null,
	'disabled'  => null,
	'group'     => null,
	'size'		=> null,
	//'variant'   => $groupVariant,
	//'rounded'   => $groupRounded,
	//'px'        => $groupPx,
	//'w'         => $groupW,
	//'title'     => null
	'gx'    	=> 0,
	'hidden'	=> false
])

@php
	$group = $group ?? $size;
@endphp


<div
	id="{{$id}}"
	{{$attributes->filter(fn ($value, $key) => $key == 'class')->class([
		'chooser',
		'noselect',
		'chooser-'.$group => $group
	])}}
	{{$disabled ? 'disabled' : ''}}
	{{$hidden ? 'hidden' : ''}}
	>
	<div
		@class([
			'row',
			'h100',
			'gx-'.$gx,
		])>
		{{$slot}}
	</div>
</div>



<script type="module">
	const chooser = $('#'+'{{$id}}'),
		isMany = !!'{{isset($many)}}';
	
	if (isMany) {
		$(chooser).find('[chooseritem]').on(tapEvent, function(e) {
			if ($(this).hasClass('chooser__item_active')) {
				$(this).removeClass('chooser__item_active');
				$(this).removeAttrib('active');
			} else {
				$(this).addClass('chooser__item_active');
				$(this).setAttrib('active');
			}
		});
		
	} else {
		$(chooser).find('[chooseritem]').on(tapEvent, function(e) {
			if ($(this).hasClass('chooser__item_active')) {
				e.preventDefault();
				return false;
			}
			
			$(chooser).find('[chooseritem].chooser__item_active').removeClass('chooser__item_active');
			$(chooser).find('[chooseritem][active]').removeAttrib('active');
			$(this).addClass('chooser__item_active');
			$(this).setAttrib('active');
		});
	}
	
</script>