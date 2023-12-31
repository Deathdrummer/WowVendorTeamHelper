@aware([
    'settings'  => null,
    'groupWrap' => null,
])

@props([
    'id'            => 'textarea'.rand(0,9999999),
    'rows'          => 5,
    'disabled'      => false,
    'enabled'       => true,
    'noedit'        => false,
    'setting'       => false,
    'savedelay'     => 500,
    'group'         => $groupWrap,
    'size'          => $groupWrap,
    'label'         => null,
    'noresize'      => false,
    'action'        => 'setSetting'
])


@php
    $group = $group ?? $size;
@endphp

<div {{$attributes->class([
        'textarea',
        $group.'-textarea' => $group,
        ($group ? $group.'-' : '').'textarea_noempty' => $setValue($value, $settings, $setting),
        ($group ? $group.'-' : '').'textarea_disabled' => $group && ($disabled || !$enabled),
    ])}}>
    
    @if($label)
        <label
            @class([
                'textarea__label',
                $group.'-textarea__label' => $group,
                'noselect'
            ])
            for="{{$id}}"
         >{{$label}}</label>    
    @endif
    
    <textarea
        @if($name)name="{{$name}}" @endif
        {{$clearAttr($attributes->whereStartsWith('input-'), 'input-', '')}}
        @isset($id) id="{{$id}}"@endisset
        rows="{{$rows}}"
        placeholder="{{$placeholder}}"
        @if($disabled || !$enabled)disabled @endif
        @if($noedit)noedit @endif
        @if($setting)oninput="$.{{$action}}(this, '{{$setting}}', {{$savedelay}})" @endif
        @if(isset($actionFunc) && !$setting)oninput="$.{{$actionFunc}}(this{{isset($actionParams) ? ', '.$actionParams : null}})" @endif
        @isset($group) inpgroup="{{$group}}" @endisset
        @class(['textarea_noresize' => $noresize])
        @if($tag) {!!$tag!!} @endif
        >{{$setValue($value, $settings, $setting)}}</textarea>
    <div class="{{($group ? $group.'-' : '').'textarea__errorlabel'}}" errorlabel></div>
</div>