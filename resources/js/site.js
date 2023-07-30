import './bootstrap';
import '@plugins/notify.min';
import '@plugins/jquery.maskedinput';
import '@plugins/jquery.number.min';
import '@plugins/jquery.scrollstop.min';
import '@plugins/jquery.mousewheel';
import '@plugins/dropdown';
import '@plugins/ddrFormSubmit';
import '@plugins/ddrInputs';
import '@plugins/ddrFiles';
//require('@plugins/ddrInputsChain');
import '@plugins/ddrWaitBlock';
import '@plugins/ddrPopup';
import '@plugins/ddrDatepicker';
import '@plugins/ddrCRUD';
import '@plugins/card';
import '@plugins/ddrScrollX';
import '@plugins/tooltip';
import '@plugins/ddrTable';
import '@plugins/blockTable';
import '@plugins/ddrContextMenu';
import '@plugins/jquery.ui';
import '@plugins/ddrCalc';
import '@plugins/ddrQuery';
import '@plugins/ddrPagination';






$.notify.defaults({
	clickToHide: true,
	autoHide: true,
	autoHideDelay: 15000,
	arrowShow: true,
	arrowSize: 5,
	//position: '...',
	elementPosition: 'top right',
	globalPosition: 'bottom right',
	style: 'bootstrap',
	className: 'success',
	showAnimation: 'fadeIn',
	showDuration: 200,
	hideAnimation: 'fadeOut',
	hideDuration: 100,
	gap: 2
});


// Configure time between final scroll event and
// `scrollstop` event to 650ms (default is 250ms).
$.event.special.scrollstop.latency = 20;








$(function() {
	
	$('body').on('contextmenu', function(e) {
		e.preventDefault();
	});
	
	
	/*ddrQuery.post('/slack/send_message', {
		webhook: 'https://hooks.slack.com/services/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',
		text: 'этот текст передан через аргумент'
	});*/

	
	
});