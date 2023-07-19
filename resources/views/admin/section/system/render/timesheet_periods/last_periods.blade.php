@if(count($list))
	<ul class="d-flex flex-row-reverse timesheetperiods">
		@foreach($list as $item)
			<li
				@class([
					'h4rem',
					'w5rem',
					'd-flex',
					'align-items-center',
					'justify-content-center',
					'border-all',
					'border-radius-5px',
					'breakword',
					'p3px',
					'pointer',
					'mr15px' => !$loop->first,
					'noselect'
				])
				onclick="$.timesheetPeriodsBuild(this, {{$item['id'] ?? null}})"
				timesheetperiod="{{$item['id'] ?? null}}"
				title="{{$item['title'] ?? ''}}"
				>
				<p class="fz14px lh90 text-center">{{$item['title'] ?? ''}}</p>
				<small class="counter" timesheetperiodscounter>{{$item['timesheet_items_count'] ?? 0}}</small>
			</li>
		@endforeach
	</ul>
@else
	<div class="d-flex align-items-center h100">
		<p class="color-gray text-center">Нет периодов</p>
	</div>
@endif