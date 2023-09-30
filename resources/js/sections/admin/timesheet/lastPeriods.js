export async function getLastTimesheetPeriods(cb = null, choosedPeriod = null, search = null) {
	const viewsPath = 'admin.section.system.render.timesheet_periods';
	
	const {data, error, status, headers} = await axios.get('crud/timesheet_periods/last_periods', {params: {views: viewsPath, choosed_period: choosedPeriod?.value, search}});
	
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	
	if ($('#lastTimesheetPeriodsBlock').find('#lastTimesheetPeriodsPlacer').length) {
		$('#lastTimesheetPeriodsPlacer').html(data);
	} else {
		$('#lastTimesheetPeriodsBlock').prepend(`<div id="lastTimesheetPeriodsPlacer">${data}</div>`);
	}
	
	callFunc(cb);
}