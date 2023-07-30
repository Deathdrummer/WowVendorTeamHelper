
window.selectText = function(elem) {
	if (window.getSelection) {
		window.getSelection().removeAllRanges();
	} else if (document.selection) {
		document.selection.empty();
	}
	
	let selection = window.getSelection();
	let range = document.createRange();
	range.selectNodeContents(elem);
	selection.removeAllRanges();
	selection.addRange(range);
}




window.getSelectionStr = function(toString = true) {
	var text = "";
	if (window.getSelection) {
		text = toString ? window.getSelection().toString() : window.getSelection();
	} else if (document.selection && document.selection.type != "Control") {
		text = document.selection.createRange().text;
	}
	return text;
}




window.removeSelection = function() {
	if (window.getSelection) {
		if (window.getSelection().empty) {  // Chrome
			window.getSelection().empty();
		} else if (window.getSelection().removeAllRanges) {  // Firefox
			window.getSelection().removeAllRanges();
		}
	} else if (document.selection) {  // IE?
		document.selection.empty();
	}
}