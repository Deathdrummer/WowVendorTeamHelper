@if($history)
	<div class="scrollblock" style="max-height: calc(100vh - 120px)">
		<div class="ddrlist">
			@foreach($history as $row)
				<div class="ddrlist__item mt2rem">
					<div class="row maxw100">
						<div class="col-auto fb20rem pt3px">
							@if($row['user_type'] == 1)
								<p class="color-green mb5px">{{$row['author']['name'] ?? $row['author']['pseudoname']}} <sup class="fz10px color-gray-400">оператор</sup></p>
							@elseif($row['user_type'] == 2)
								<p class="color-red mb5px">{{$row['author']['name'] ?? $row['author']['pseudoname']}} <sup class="fz10px color-gray-400">админ</sup></p>
							@endif
							
							<p class="fz12px color-gray-500">{{DdrDateTime::date($row['created_at'])}} в  {{DdrDateTime::time($row['created_at'])}}</p>
						</div>
						<div class="col">
							<p class="difftext">{!!$row['data']!!}</p>
						</div>
					</div>
				</div>
				
				@if(!$loop->last)
					<hr class="mt2rem hr-light">
				@endif
			@endforeach
		</div>
	</div>
@else
	<p class="color-gray text-center fz16px">Нет данных</p>
@endif

