$.fn.card = function(comand, ...params) {
	const selector = this,
		comands = {
			ready() {
				$(selector).find('[cardwait]').addClass('card__wait_closing');
				setTimeout(() => {
					$(selector).find('[cardwait]').setAttrib('hidden');
				}, 500);
			},
			wait() {
				$(selector).find('[cardwait]').removeAttrib('hidden');
				$(selector).find('[cardwait]').addClass('card__wait_opening');
			},
			disableButton() {
				$(selector).find('[cardbutton]').ddrInputs('disable');
			},
			enableButton() {
				$(selector).find('[cardbutton]').ddrInputs('enable');
			},
			scrolled(height = null) {
				if (!height) return;
				$(selector).find('[scrollblock]').css('max-height', height);
			}
		};
	
	
	
	
	
	
	
	comands[comand](...params);
	return comands;
}