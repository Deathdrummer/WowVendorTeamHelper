@aware([
	'settings' 	=> null,
	'groupWrap'	=> null,
])

@props([
	'checked' 	=> false,
	'value' 	=> false,
	'id' 		=> 'checkbox'.rand(0,9999999),
	'disabled'	=> false,
	'setting' 	=> false,
	'group' 	=> $groupWrap,
	'size'		=> $groupWrap,
	'label' 	=> null,
    'action' 	=> 'setSetting',
])

@php
	$group = $group ?? $size;
@endphp


<div {{$attributes->class([
		'checkbox',
		$group.'-checkbox' => $group,
		($group ? $group.'-' : '').'checkbox_checked' => $checked ?: $setChecked($checked, $settings, $setting),
		($group ? $group.'-' : '').'checkbox_disabled' => $disabled
	])}}>
	
	<input
		{{$clearAttr($attributes->whereStartsWith('ddr-'), 'ddr-', '')}}
		type="checkbox"
		@if($name)name="{{$name}}" @endif
		@if($setting)oninput="$.setSetting(this, '{{$setting}}')" @endif
		id="{{$id}}"
		@checked($checked ?: $setChecked($checked, $settings, $setting)) 
		@if($value)value="{{$value}}" @endif
		@if($disabled)disabled @endif
		@isset($group) inpgroup="{{$group}}" @endisset
		@if($setting)oninput="$.{{$action}}(this{{isset($actionParams) ? ', '.$actionParams : null}})"@endif
		@if($actionFunc && !$setting)oninput="$.{{$actionFunc}}(this{{isset($actionParams) ? ', '.$actionParams : null}})" @endif
		@if($tag) {!!$tag!!} @endif
		>
	
	<label class="noselect" for="{{$id}}"></label>
	
	<label
		for="{{$id}}"
		@class([
			'checkbox__label',
			'lh90',
			'd-inline-block',
			$group.'-checkbox__label' => $group,
			'noselect'
		])
		>{!!$label!!}</label>
	<div class="{{($group ? $group.'-' : '').'checkbox__errorlabel'}}" errorlabel></div>
</div>