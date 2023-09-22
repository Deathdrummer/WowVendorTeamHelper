$.fn.ddrBuildInputsData = function(params = []) {
	let selector = this,
		saveUserDataTOut,
		prevInp,
		abortContr;
	
	const {
		attr,
		timeout,
		onBefore,
		onChange,
		//after,
		//abortContr,
	} = _.assign({
		attr: 'name',
		timeout: 300,
		onBefore: null,
		onChange: null,
		//after: null,
		//abortContr: null,
	}, params);
	
	if (!attr) throw new Error('ddrBuildInputsData ошибка ->  не указан attr');
	
	/*const createAbortCtrl = () => {
		const abCtrl = new AbortController();
		abortContr = abCtrl;
		return abCtrl;
	}*/
	
	
	let executed = false;
	$(selector).ddrInputs('change', function(inp, e) {
		callFunc(onBefore, inp, e);
		
		const noTextTypes = ['checkbox', 'radio', 'select-one'].includes(e?.target?.type);
		
		
		//console.log(executed);
		if (prevInp == inp) {
			//abortContr.abort();
			clearTimeout(saveUserDataTOut); 
			//executed = false;
			//return false;
		} else if (prevInp !== inp) {
			prevInp = inp;
		}
		
		
		let type = e?.target?.type;
		
		let isChecked = e?.target?.checked;
		let tOut = noTextTypes ? 0 : timeout;
		
		const done = () => {
			if (noTextTypes) {
				$(inp).ddrInputs('touch');
			}
		}
		
		
		saveUserDataTOut = setTimeout(() => {
			let sd = $(inp).attr(attr).split(':'),
				setting = sd[0] || null,
				sType = ['checkbox'].includes(e?.target?.type) ? 'arr' : 'single'; // arr arrassoc single
			
			const value = $(inp).val();
			const remove = type == 'checkbox' && !isChecked;
			
			if (noTextTypes) {
				$(inp).ddrInputs('notouch');
			}
			
			callFunc(onChange, {setting, value, type: sType, remove, inp, done});
		}, tOut);
	});
}