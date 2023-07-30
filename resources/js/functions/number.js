
/*
	Счетчик
		- начало отсчета
		- направление
		- шаг
*/
window.Counter = function(start, order, step) {
	var count = start || 0;
	return function(num) {
		count = num != undefined ? num : count;
		return order == undefined || order == '+' ? (order == '-' ? count-=step : count+=step) : count-=step;
	}
}


