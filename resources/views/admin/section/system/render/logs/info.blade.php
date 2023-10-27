@if($hasUpdated)
	<table class="eventslogstable">
		<thead>
			<tr>
				<td class="w20rem"><strong>Поле</strong></td>
				<td><strong>Значение</strong></td>
				<td><strong>Обновленное значение</strong></td>
			</tr>
		</thead>
		<tbody>
			@foreach($data as $field => $row)
				<tr>
					<td class="field"><p>{{$row['title']}}</p></td>
					<td>
						@if($row['data'] ?? null)
							@if($row['meta']['symbal'] ?? false)
								@if ($row['meta']['symbal'] == 'rub')
									<p>{{$row['data']}} @symbal(rub)</p>
								@elseif ($row['meta']['symbal'] == 'dollar')
									<p>{{$row['data']}} @symbal(dollar)</p>
								@endif
							@elseif($row['meta']['date'] ?? false)
								<p class="fz12px">{{DdrDateTime::date($row['data'] ?? null, ['shift' => '-'])}} в {{DdrDateTime::time($row['data'] ?? null, ['shift' => '-'])}}</p>
							@else
								<p>{{$row['data']}}</p>
							@endif
						@else
							<p>-</p>
						@endif
					</td>
					<td>
						@if($row['updated'] ?? null)
							@if($row['meta']['symbal'] ?? false)
								@if ($row['meta']['symbal'] == 'rub')
									<p>{{$row['data']}} @symbal(rub)</p>
								@elseif ($row['meta']['symbal'] == 'dollar')
									<p>{{$row['data']}} @symbal(dollar)</p>
								@endif
							@elseif($row['meta']['date'] ?? false)
								<p class="fz12px">{{DdrDateTime::date($row['updated'] ?? null, ['shift' => '-'])}} в {{DdrDateTime::time($row['updated'] ?? null, ['shift' => '-'])}}</p>
							@else
								<p>{{$row['updated']}}</p>
							@endif
						@else
							<p>-</p>
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	<table class="eventslogstable">
		<thead>
			<tr>
				<td class="w20rem"><strong>Поле</strong></td>
				<td><strong>Значение</strong></td>
			</tr>
		</thead>
		<tbody>
			@foreach($data as $field => $row)
				<tr>
					<td class="field"><p>{{$row['title']}}</p></td>
					<td>
						@if($row['data'] ?? null)
							@if($row['meta']['symbal'] ?? false)
								@if ($row['meta']['symbal'] == 'rub')
									<p>{{$row['data']}} @symbal(rub)</p>
								@elseif ($row['meta']['symbal'] == 'dollar')
									<p>{{$row['data']}} @symbal(dollar)</p>
								@endif
							@elseif($row['meta']['date'] ?? false)
								<p class="fz12px">{{DdrDateTime::date($row['data'] ?? null)}} в {{DdrDateTime::time($row['data'] ?? null)}}</p>
							@else
								<p>{{$row['data']}}</p>
							@endif
						@else
							<p>-</p>
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif