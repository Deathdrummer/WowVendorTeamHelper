@aware([
	'settings' 	=> null,
	'groupWrap'	=> null,
])

@props([
	'id' 			=> 'file'.rand(0,9999999),
	'disabled' 		=> false,
	'onselect'		=> false,
	'setting' 		=> false,
	'group'			=> $groupWrap,
	'size'			=> $groupWrap,
    'action'        => 'setSetting'
])





<div>
	<input {{$clearAttr($attributes->whereStartsWith('input-'), 'input-', '')}} type="file" hidden id="{{$id}}"{{$isMultiple()}}>
	{{$slot}}
</div>





{{-- 
<div {{$attributes->class('file')}}>
	<label for="{{$id}}" class="{{$group ? $group.'-' : ''}}file__label">
		<div class="{{$group ? $group.'-' : ''}}file__image" ddrfileimage></div>
		<p class="{{$group ? $group.'-' : ''}}file__empty">{{$setEmptyText()}}</p>
	</label>
	<input
		type="file"
		multiple
		name="{{$name}}"
		id="{{$id}}"
		{{$setInpGroup()}}
		{{$isMultiple()}}
		@if($tag) {!!$tag!!} @endif
		>
</div> --}}