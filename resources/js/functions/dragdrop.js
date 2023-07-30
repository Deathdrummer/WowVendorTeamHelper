



// Обрабатываем событие Drop
	$('body').off('drop', '#singleFileDrop').on('drop', '#singleFileDrop', function(event) {
		event.preventDefault();
		event.stopPropagation();
		
		let files;
		
		if (event.originalEvent.dataTransfer != undefined) files = event.originalEvent.dataTransfer.files;
		else if (event.dataTransfer != undefined) files = event.dataTransfer.files;
		else if (event.currentTarget.files != undefined) files = event.currentTarget.files;
		else $(o.dropZone).addClass('ddrdragfiles__dropzone_error');
		
		//$('.ddrdragfiles__dropzone_error').removeClass('ddrdragfiles__dropzone_error');
		//$('.ddrdragfiles__dropzone').addClass('ddrdragfiles__dropzone_drop');
		//$(this).removeClass('ddrdragfiles__dropzone_hover');
		//getFiles(e);
		
		console.log(files);
	});
	
	
	
	let dragstat = false;
	// Добавляем класс hover при наведении
	$('body').off('dragover', '#singleFileDrop').on('dragover', '#singleFileDrop', function(e) {
		if (!dragstat) {
			console.log('dragover');
			dragstat = true;
		}
		event.preventDefault();
		event.stopPropagation();
		return false;
	});
	
	// Убираем класс hover
	$('body').off('dragleave', '#singleFileDrop').on('dragleave', '#singleFileDrop', function(e) {
		if (dragstat) {
			console.log('dragleave');
			dragstat = false;
		}
		event.preventDefault();
		event.stopPropagation();
		return false;
	});
	