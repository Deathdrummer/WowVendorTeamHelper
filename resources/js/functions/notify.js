window.notify = function(message = '', className = 'success', timeOut, ops = {}) {
	if (!_.isString(className) && _.isNumber(className)) {
		timeOut = className;
		className = 'success';
	} 
	
	let icon = 'fa fa-info';

	if (className == 'success') {
		icon = 'fa fa-check-circle';
	} else if (className == 'error') {
		icon = 'fa fa-exclamation-triangle';
	} else if (className == 'info') {
		icon = 'fa fa-info-circle';
	}

	$.notify.addStyle('ddr', {
		html: '<div><i class="'+icon+'"></i><span data-notify-text/></div>'
	});

	$.notify(message, {
		clickToHide: ops.clickToHide || true,
		autoHide: true,
		autoHideDelay: timeOut ? (timeOut * 1000) : 5000,
		arrowShow: false,
		arrowSize: 15,
		position: 'top right',
		style: 'ddr',
		className: className,
		showAnimation: 'fadeIn',
		showDuration: 200,
		hideAnimation: 'fadeOut',
		hideDuration: 100,
		gap: 2
	});
	
	return {
		close() {
			$('.notifyjs-ddr-base').trigger('notify-hide');
		}
	} 
}










window.processNotify = function(message = null) {
	if (_.isNull(message)) return;
	const waitNotifyWrapper = $('body').find('[waitnotify]');
	
	let waitNotifyHtml = '<div class="waitnotify__item" waitnotifyitem>' +
							'<div class="waitnotify__iconcontainer">' +
								'<div class="waitnotify__icon" waitnotifyitemwait><img src="'+loadingIcon+'" ddrwaiticon></div>' +
							'</div>' +
							'<div class="waitnotify__message" waitnotifymessage>'+message+'</div>' +
						 '</div>';
	
	const waitNotifyDOM = $(waitNotifyHtml);
	
	if ($(waitNotifyWrapper).length == 0) {
		$('body').append('<div class="waitnotify noselect" waitnotify></div>');
		$('[waitnotify]').html(waitNotifyDOM);
	} else {
		$(waitNotifyWrapper).append(waitNotifyDOM); // prepend
	}
	
	$(waitNotifyDOM).on(tapEvent, function() {
		$(this).remove();
	});
	
	$.extend(waitNotifyDOM, {
		done(params) {
			const item = this,
				{message, close, iconFa, icon} = _.assign({
					message: null,
					iconFa: '<i class="fa-regular fa-fw fa-circle-check"></i>',
					icon: null,
					close: 5
				}, params);
			
			$(item).addClass('waitnotify__item_done');
			if (message) $(item).find('[waitnotifymessage]').html(message);
			$(item).find('[waitnotifyitemwait]').html(icon || iconFa);
			
			setTimeout(() => {
				$(item).remove();
			}, close * 1000);
		},
		cancelled(params) {
			const item = this,
				{message, close, iconFa, icon} = _.assign({
					message: null,
					iconFa: '<i class="fa-solid fa-fw fa-ban"></i>',
					icon: null,
					close: 5
				}, params);
			
			$(item).addClass('waitnotify__item_cancelled');
			if (message) $(item).find('[waitnotifymessage]').html(message);
			$(item).find('[waitnotifyitemwait]').html(icon || iconFa);
			
			setTimeout(() => {
				$(item).remove();
			}, close * 1000);
		},
		error(params) {
			const item = this,
				{message, close, iconFa, icon} = _.assign({
					message: null,
					iconFa: '<i class="fa-solid fa-fw fa-triangle-exclamation"></i>',
					icon: null,
					close: 5
				}, params);
			
			$(item).addClass('waitnotify__item_error');
			if (message) $(item).find('[waitnotifymessage]').html(message);
			$(item).find('[waitnotifyitemwait]').html(icon || iconFa);
			
			setTimeout(() => {
				$(item).remove();
			}, close * 1000);
		}
	});
	
	return waitNotifyDOM;
}