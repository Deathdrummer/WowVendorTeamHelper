$.copyToClipboard = (e, copyData = null) => {
	e.stopPropagation();
	copyStringToClipboard(copyData || $(e.target).text());
	//$.notify('Скопировано! copyToClipboard');
}



/*
	Вызвать событие нативного копирование при отсутсвующем выделении
*/
window.ddrCopy = (callback = false, rule = false) => {
	let selection = null;
	const os = getOS();
	
	$(document).on('copy', function(e) {
		if (rule()) {
			selection = getSelectionStr();
			
			if (os == 'Windows' && !selection) {
				if (callback && _.isFunction(callback)) callback();
			
			} else if (os == 'MacOS' && !selection) {
				if (callback && _.isFunction(callback)) callback();
			}
		}
	});
}




/*
	Скопировать в буфер обмена 
		- строка для копирования
*/
window.copyStringToClipboard = function(str = null) {
	if (_.isNull(str)) return false;
	
	if (navigator.clipboard) {
		navigator.clipboard.writeText(str);
	} else {
		let el = document.createElement('textarea');
		el.value = str;
		el.setAttribute('readonly', '');
		el.style.position = 'absolute';
		el.style.left = '-9999px';
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	}	
}





