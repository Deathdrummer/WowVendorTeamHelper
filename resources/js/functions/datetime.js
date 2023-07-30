window.calendar = function(calendarId = null, params = {}) {
	if (_.isNull(calendarId)) return;
	
	const {initDate, onShow, onSelect, formatter} = _.assign({
		initDate: null, // год месяц день
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
		
		onShow: ({calendar}) => {
			$(calendar).parent('.qs-datepicker-container').css({
				'box-shadow': 'none',
				'position': 'static',
				'width': '220px',
			});
			
			$(calendar).css({
				'position': 'relative',
			});
			
			callFunc(onShow, calendar);
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
	var monthNames = {1: 'января', 2: 'февраля', 3: 'марта', 4: 'апреля', 5: 'мая', 6: 'июня', 7: 'июля', 8: 'августа', 9: 'августа', 10: 'октября', 11: 'ноября', 12: 'декабря'};

	const d = dateStr ? new Date(dateStr) : new Date();
	
	const year = {
		short: d.getFullYear().toString().substr(-2),
		full: d.getFullYear(),
	};
	
	const month = {
		short: d.getMonth() + 1,
		zero: addZero(d.getMonth() + 1),
		named: monthNames[d.getMonth() + 1],
	};
	
	const day = {
		short: d.getDate(),
		zero: addZero(d.getDate()),
	};
	

	return {
		year,
		month,
		day,
		hours: d.getHours(),
		minutes: d.getMinutes(),
		seconds: d.getSeconds(),
	};
};
