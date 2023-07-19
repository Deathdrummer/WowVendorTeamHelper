export async function getLastTimesheetPeriods(cb = null) {
	const viewsPath = 'admin.section.system.render.timesheet_periods';
	
	const {data, error, status, headers} = await axios.get('crud/timesheet_periods/last_periods', {params: {views: viewsPath}});
	
	if (error) {
		console.log(error);
		$.notify(error?.message, 'error');
		return;
	}
	
	$('#lastTimesheetPeriodsBlock').html(data);
	
	callFunc(cb);
}