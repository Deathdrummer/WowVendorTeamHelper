<div
	@class([
		'chat__item',
		'chat__item-right' => $self,
		'chat__item-left' => !$self,
		'mr1rem',
	])
	>
	
	<div class="chat__avatar">
		<p class="color-black fz20px">{{$account_id ?? null}}</p>
	</div>
	
	<div
		@class([
			'chat__post',
			'p1rem',	
			'border-all',
			'border-light',
		])
		>
		<strong class="chat__author breakword select-text">{{$self ? ($author['name'] ?? null) : ($author['pseudoname'] ?? null)}}</strong>
		<p class="chat__message breakword select-text">{{$message ?? null}}</p>
		<small class="fz10px color-gray-500 chat__date mt5px">{{DdrDateTime::date($created_at ?? null)}} в {{DdrDateTime::time($created_at ?? null)}}</small>
	</div>
</div>