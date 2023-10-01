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
					'noselect',
					'active' => $item['id'] == ($choosedPeriod ?? null) && ($item['timesheet_items_count'] ?? 0) > 0,
					'active-empty' => $item['id'] == ($choosedPeriod ?? null) && ($item['timesheet_items_count'] ?? 0) == 0,
					'disabled' => $search && !($item['timesheet_items_count'] ?? 0),
				])
				onclick="$.timesheetPeriodsBuild(this, {{$item['id'] ?? null}}, {{($item['timesheet_items_count'] ?? 0) ? 1 : 0}})"
				timesheetperiod="{{$item['id'] ?? null}}"
				title="{{$item['title'] ?? ''}}"
				>
				<p class="fz14px lh90 text-center">{{$item['title'] ?? ''}}</p>
				@if(!$search || ($search && $item['timesheet_items_count'] ?? 0))
					<small
						@class([
							'counter',
							'counter-searched'	=> $search
						])
						timesheetperiodscounter
						>{{$item['timesheet_items_count'] ?? 0}}</small>
				@endif
			</li>
		@endforeach
	</ul>
@else
	<div class="d-flex align-items-center h100">
		<p class="color-gray text-center">Нет периодов</p>
	</div>
@endif