@props([
    'checked'       => false,
    'id'            => 'checklabel'.rand(0,9999999),
    'wrapper'       => 'checklabelwrapper'.rand(0,9999999),
    'name'          => null,
    'value'         => null,
    'action'        => null,
    'checkedclass'  => null,
    'inistyle'      => null,
    'checkedstyle'  => null,
])

<div class="checklabel" id="{{$wrapper}}">
    <input
        hidden
        type="checkbox"
        @if($checked)checked @endif
        id="{{$id}}input"
        @if($name)name="{{$name}}" @endif
        @if($value)value="{{$value}}" @endif
        @if($action)oninput="$.{{$action}}(this);"@endif
        {{$attributes->whereDoesntStartWith(['class', 'style'])}}
        @if($tag) {!!$tag!!} @endif
        >
    <div
        id="{{$id}}block"
        {{$attributes->class([$checkedclass => $checked])}}
        @if($inistyle && !$checked)style="{{$inistyle}}"@endif
        @if($checkedstyle && $checked)style="{{$checkedstyle}}"@endif
        ><label class="pointer" checklabel for="{{$id}}input"></label>{{$slot}}</div>
</div>


<script type="module">
    const block = $('#{{$id}}container'),
        checkedClass = '{{$checkedclass ?? null}}',
        initStyle = '{{$inistyle ?? null}}',
        checkedStyle = '{{$checkedstyle ?? null}}';
        
    $('#{{$id}}input').on('input', (e) => {
        const isChecked = e.target.checked;
        
        if (checkedClass) {
            if (isChecked) $('#{{$id}}block').addClass(checkedClass);
            else $('#{{$id}}block').removeClass(checkedClass);
        }
        
        if (checkedStyle) {
            if (isChecked) $('#{{$id}}block').setAttrib('style', checkedStyle);
            else $('#{{$id}}block').setAttrib('style', initStyle);
        }
    });
</script>