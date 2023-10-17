window.dateUTC = function(incomingDate = null) {
	let dtLocal = incomingDate || new Date();
	return new Date(dtLocal.toISOString().split('Z')[0]);
}

window.dateStartOfDay = function(d = null) {
	if (d instanceof Date == false) d = new Date(d);
	return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0, 0);
}

window.dateEndOfDay = function(d = null) {
	if (d instanceof Date == false) d = new Date(d);
	return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59, 999);
}


window.dateToTimestamp = function(date = null, options = {}) {
	if (!date || date == 'now') date = new Date(Date.now());
	if (date instanceof Date == false) date = new Date(date);
	
	
	const y = date?.getFullYear(),
		m = addZero(date?.getMonth() + 1),
		d = addZero(date?.getDate()),
		h = addZero(date?.getHours()),
		min = addZero(date?.getMinutes()),
		s = addZero(date?.getSeconds());
	
	if (options?.correct == 'startOfDay') return y+'-'+m+'-'+d+' 00:00:00';
	if (options?.correct == 'endOfDay') return y+'-'+m+'-'+d+' 23:59:59:999';
	return y+'-'+m+'-'+d+' '+h+':'+min+':'+s;
}



window.calendar = function(calendarId = null, params = {}) {
	if (_.isNull(calendarId)) return;
	
	const {initDate, minDate, maxDate, onShow, onSelect, formatter} = _.assign({
		initDate: null, // год месяц день
		maxDate: null, // год месяц день
		minDate: null, // год месяц день
		onShow: null,
		onSelect: null,
		formatter: null,
	}, params);
	
	const datePicker = ddrDatepicker('#'+calendarId, {
		id: 'calendar',
		alwaysShow: true,
		position: false,
		startDay: 1,
		defaultView: 'calendar',
		overlayPlaceholder: 'Введите год',
		customDays: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
		customMonths: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
		dateSelected: initDate ? (initDate instanceof Date ? initDate : (initDate == 'now' ? new Date(Date.now()) : new Date(initDate))) : null,
		minDate,
		maxDate,
		onShow: ({calendar, setMin, setMax, setDate}) => {
			$(calendar).parent('.qs-datepicker-container').css({
				'box-shadow': 'none',
				'position': 'static',
				'width': '220px',
			});
			
			$(calendar).css({
				'position': 'relative',
			});
			
			//console.log(setMin);
			
			callFunc(onShow, calendar, {setMin, setMax, setDate});
		    // Do stuff when the calendar is shown.
		    // You have access to the datepicker instance for convenience.
		},
		onSelect: (instance, date) => {
			if (!onSelect) {
				const {year, month, day,} = ddrDateBuilder(date);
				if (date) $(instance.el).attr('date', day.zero+'-'+month.zero+'-'+year.full);
				else $(instance.el).removeAttrib('date');
			}
			
			callFunc(onSelect, instance, date);
		},
		formatter: (input, cd, instance) => {
			//$(input).setAttrib('date', addZero(cd.getDate())+'-'+addZero(cd.getMonth() + 1)+'-'+cd.getFullYear());
			$(input).setAttrib('date', cd);
			callFunc(formatter, input, cd, instance);
		},
		
	});
	return datePicker;
}











window.ddrDateBuilder = function(dateStr = false) {
	const monthNames = {1: 'января', 2: 'февраля', 3: 'марта', 4: 'апреля', 5: 'мая', 6: 'июня', 7: 'июля', 8: 'августа', 9: 'сентября', 10: 'октября', 11: 'ноября', 12: 'декабря'};
	const monthNamesShort = {1: 'янв', 2: 'фев', 3: 'мар', 4: 'апр', 5: 'мая', 6: 'июн', 7: 'июл', 8: 'авг', 9: 'сен', 10: 'окт', 11: 'ноя', 12: 'дек'};
	const d = dateStr ? new Date(dateStr) : new Date();
	
	const year = {
		short: d.getFullYear().toString().substr(-2),
		full: d.getFullYear(),
	};
	
	const month = {
		short: d.getMonth() + 1,
		zero: addZero(d.getMonth() + 1),
		named: monthNames[d.getMonth() + 1],
		namedShort: monthNamesShort[d.getMonth() + 1],
	};
	
	const day = {
		short: d.getDate(),
		zero: addZero(d.getDate()),
	};
	
	const minutes = {
		short: d.getMinutes(),
		zero: addZero(d.getMinutes()),
	};
	
	const hours = {
		short: d.getHours(),
		zero: addZero(d.getHours()),
	};
	
	const seconds = {
		short: d.getSeconds(),
		zero: addZero(d.getSeconds()),
	};
	

	return {
		year,
		month,
		day,
		hours,
		minutes,
		seconds,
	};
};
