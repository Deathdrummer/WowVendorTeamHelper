// Задать куки
window.setCookie = function(cname, cvalue, exdays, encode) {
	if (encode) {
		cvalueStr = (cvalue * 567)+'346045267804235468667352353ddr';
		let bta = encodeURIComponent(btoa(cvalueStr.replace(/%([0-9A-F]{2})/g, function toSolidBytes(match, p1) {
			  return String.fromCharCode('0x' + p1);
		})));
		cvalue = bta;			
	}
	
	// expire the old cookie if existed to avoid multiple cookies with the same name
	if  (getCookie(cname)) {
		document.cookie = cname + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
	}
	let d = new Date();
	d.setTime(d.getTime() + ((exdays || 365) * 24 * 60 * 60 * 1000));
	let expires = "expires=" + d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
}


// Получить куки
window.getCookie = function(cname, decode) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ')
			c = c.substring(1);
		if (c.indexOf(name) == 0) {
			let cookie = c.substring(name.length, c.length);
			if (decode) {
				let atb = decodeURIComponent(atob(decodeURIComponent(cookie)).split('').map(function (c) {
					return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
				}).join(''));
				return atb.replace('346045267804235468667352353ddr', '') / 567;
			}
			return decodeURIComponent(cookie);
		}
	}
	return false;
}



window.deleteCookie = function(name, path) {
	if (getCookie(name)) {
		document.cookie = name+"="+((path) ? ";path="+(path || '/'):"")+";expires=Thu, 01 Jan 1970 00:00:01 GMT";
	}
}