window.loadSectionScripts = async (ops = {}) => {
	if (_.isEmpty(ops)) throw Error('loadSectionScripts -> не переданы параметры!');
	
	const {
		guard,
		section,
	} = _.assign({
		guard: 'site',
		section: null,
	}, ops);
	
	if (_.isNull(section)) throw Error('loadSectionScripts -> не указан параметр section');
	
	return await import(`./sections/${guard}/${section}/index.js`);
}


/*
window.loadSyncSectionScripts = (ops = {}) => {
	if (_.isEmpty(ops)) throw Error('loadSectionScripts -> не переданы параметры!');
	
	const {
		guard,
		section,
	} = _.assign({
		guard: 'site',
		section: null,
	}, ops);
	
	if (_.isNull(section)) throw Error('loadSectionScripts -> не указан параметр section');
	
	return import(`./sections/${guard}/${section}/index.js`);
}*/