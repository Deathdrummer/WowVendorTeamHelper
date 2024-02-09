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
		const type = e?.target?.type,
			noTextTypes = ['checkbox', 'radio', 'select-one'].includes(type);
		
		
		//console.log(executed);
		if (prevInp == inp) {
			//abortContr.abort();
			clearTimeout(saveUserDataTOut); 
			//executed = false;
			//return false;
		} else if (prevInp !== inp) {
			prevInp = inp;
		}
		
		
		const done = () => {
			if (noTextTypes) {
				$(inp).ddrInputs('touch');
			}
		}
		
		let value = null;
		
		saveUserDataTOut = setTimeout(() => {
			let sd = $(inp).attr(attr).split(':'),
				setting = sd[0] || null,
				sType = ['checkbox'].includes(type) ? (sd[1] || 'arr') : 'single'; // arr arrassoc single
			
			
			let val = $(inp).val(),
				remove = false;
			
			if (['checkbox'].includes(type)) {
				let isChecked = e?.target?.checked;
				val = val.split(':');
				
				let valTrue = val[0],
					valFalse = val[1] || null;
				
				value = isChecked ? valTrue : valFalse;
				
				remove = _.isNull(valFalse) && !isChecked;
				console.log(remove, value);
			} else {
				value = $(inp).val();
				remove = !value;
			}
			
			if (noTextTypes) {
				$(inp).ddrInputs('notouch');
			}
			
			callFunc(onChange, {setting, value, type: sType, remove, inp, done});
		}, noTextTypes ? 0 : timeout);
	});
}