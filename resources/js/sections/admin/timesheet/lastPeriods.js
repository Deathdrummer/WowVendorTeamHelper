export async function getLastTimesheetPeriods(cb = null, choosedPeriod = null, search = null) {
	const viewsPath = 'admin.section.system.render.timesheet_periods';
	const sectionName = location.pathname.replace('/', '');
	
	const {data, error, status, headers} = await axios.get('crud/timesheet_periods/last_periods', {
		params: {
			views: viewsPath,
			choosed_period: choosedPeriod?.value,
			search,
			only_has_events: ['counts-stat'].includes(sectionName) ? 1 : 0,
		}
	});
	
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	const periodsCounts = JSON.parse(headers['periods_counts'] || [], true);
	
	if ($('#lastTimesheetPeriodsBlock').find('#lastTimesheetPeriodsPlacer').length) {
		$('#lastTimesheetPeriodsPlacer').html(data);
	} else {
		$('#lastTimesheetPeriodsBlock').prepend(`<div id="lastTimesheetPeriodsPlacer">${data}</div>`);
	}
	
	callFunc(cb, periodsCounts);
}