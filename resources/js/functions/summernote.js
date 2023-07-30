
/*
	Summernote
*/
window.initEditors = function() {
	var isLoaded = false,
		fontSizes = [],
		lineHeights = [];

	for (i = 0; i < 100; i++) {
		fontSizes.push(''+i);
		lineHeights.push(i+'px');
	}


	options = {
		disableDragAndDrop: true,
		height: 500,
		lang: 'en-US',
		emptyPara: '',
		lineHeights: lineHeights,
		fontSizes: fontSizes,
		codeviewFilter: false,
		codeviewIframeFilter: true,
		disableGrammar: true,
		codemirror: {
			theme: 'monokai'
		},
		toolbar: [
			['font', ['bold', 'italic', 'underline', 'clear']],
			['height', ['height']],
			['style', ['style']],
			['fontsize', ['fontsize']],
			['fontname', ['fontname']],
			['color', ['color']],
			['para', ['ul', 'ol', 'paragraph']],
			['table', ['table']],
			['insert', ['link', 'image', 'video']],
			['view', ['fullscreen', 'codeview']]
		],
		callbacks: {
			onChange: function (contents) {
				if ($(this).closest('tr').find('[save], [update]').length) {
					$(this).closest('tr').find('[save], [update]').removeAttrib('disabled');
				}
			}
			/*onChange: function (contents) {
				$summernote.summernote('code', '');
			},
			onBlurCodeview: function() {
			}*/
		},
		buttons: {
			image: function(context) {
				return $.summernote.ui.button({
					contents: '<i class="note-icon-picture"></i>',
					tooltip: 'тултип',
					className: 'editorfile',
					click: function () {
						$('#clientFileManager:not(.visible)').addClass('visible');
						currentDir = lscache.get('clientmanagerdir') || false;
						if (currentDir && !isLoaded) {
							getAjaxHtml('filemanager/files_get', {directory: currentDir, filetypes: 'png|jpg|jpeg|gif|ico|bmp', client: 1}, function(html) {
								$('#clientFilemanagerContentFiles').html(html);
							});

							getAjaxHtml('filemanager/dirs_get', {current_dir: currentDir}, function(html) {
								$('#clientFilemanagerDirs').html(html);
							});
							isLoaded = true;
						}

						$('#clientFilemanagerContentFiles').off(tapEvent, '.image').on(tapEvent, '.image', function() {
							var thisFileBlock = $(this).closest('.clientfilemanager__file'),
								thisFilePath = $(thisFileBlock).attr('dirfile'),
								thisFileName = $(thisFileBlock).attr('namefile'),
								thisFileSrc = $(this).find('img').attr('src');
							context.invoke('editor.insertImage', location.origin+'/public/filemanager/'+thisFilePath);
							$('#clientFileManager').removeClass('visible');
						});
					}
				}).render();
			}
		}
	};


	/*var selectors = [],
		editors = $('body').find('[editor]');

	if (editors.length > 0) {
		$.each(editors, function(k, item) {
			var d = $(item).attr('editor').split('|'),
				h = d[1] != undefined ? parseInt(d[1]) : 500;

			selectors.push({
				selector: $(item).attr('editor'),
				height: h
			});
		});

		$.each(selectors, function(k, s) {
			options.height = s.height;
			$('[editor="'+s.selector+'"]').summernote(options);
			$('[editor="'+s.selector+'"]').addClass('activate');
		});
	}*/

	$('body').find('[editor]:not(.activated)').summernote(options);
	$('body').find('[editor]').addClass('activated');

}