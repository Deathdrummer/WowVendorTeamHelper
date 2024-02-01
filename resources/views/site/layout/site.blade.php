<div class="app">
	<header class="header">
		<div class="header__container">
			<div class="row justify-content-between g-20">
				<div class="col-auto">
					<div class="header__block w25rem">
						<div class="header__logo">
							<div>
								<img
									src="{{Vite::asset('resources/images/logo.png')}}"
									alt="{{$company_name ?? 'BPM systems'}}"
									title="{{$company_name ?? 'BPM systems'}}"
									>
							</div>
							{{-- <p>{{$company_name}}</p> --}}
						</div>
						
						<div class="header__menu">
							<i class="fa-solid fa-fw fa-bars" id="mainNavHendler" onclick="$.mainNavHendler(event)"></i>
						</div>
					</div>
				</div>
				
				<div class="col-auto">
					<div class="header__line"></div>
				</div>
				<div class="col-auto">
					<div class="header__block">
						<p class="header__pagetitle" id="sectionTitle"></p>
					</div>
				</div>
				<div class="col-auto">
					<div class="header__block">
						<teleport id="headerTeleport"></teleport>
					</div>
				</div>
				
				
				<div class="col mr-2rem">
					<div class="row justify-content-between g-20">
						<teleport id="headerTeleport1"></teleport>
						<teleport id="headerTeleport2"></teleport>
						<teleport id="headerTeleport3"></teleport>
					</div>
				</div>
				
				@if(isset($show_locale) && $show_locale)
					<div class="col-auto">
						<div class="header__block">
							<x-localebar group="large" />
						</div>
					</div>
				@endif
				
				{{-- <div class="col-auto">
					<div class="header__block">
						@unlessverify('site')
							<x-button id="resendVerifyLinkBtn">Выслать ссылку повторно</x-button>
						@endverify
					</div>
				</div> --}}
				
				<div @class([
						'col-auto',
						//'ms-auto' => !isset($show_locale) || !$show_locale
					])>
					<div class="header__block">
						<p class="fz16px"><strong>{{$user->name ?? __('custom.anon')}}</strong></p>
					</div>
				</div>
				
				
				<div class="col-auto">
					<div class="header__block">
						@if(isset($show_nav) && $show_nav)
						<div class="header__nav" headernav>
							<div class="headernav__handler" touch="header__nav_opened">
								<i class="fa-solid fa-fw fa-bars"></i>
								{{-- <p>Меню</p> --}}
							</div>
							
							
							<nav class="headernav noselect">
								<div class="headernav__item">
									@isset($nav)
										{{-- <p>sectionTitle</p> --}}
										<ul>
											@foreach($nav as $item)
												@if (!isset($item['section']))
													@continue
												@endif
												
												<li @class([
														'active' => $activeNav == $item['section'],
														'opened' => isset($item['active'])
													])
													loadsection="{{$item['section']}}"
													><span>{{$item['title']}}</span></li>	
													
											@endforeach
											
											{{-- <li class="line"></li>
											
											<teleport id="menuTeleport"></teleport> --}}
										</ul>
									@endisset
								</div>
								
								
								{{-- {% for $sectionTitle, $sectionsList in $sections %}
									<div class="main_nav_item">
										<p>{{$sectionTitle}}</p>
										<ul>
											{% for $url, $title in $sectionsList %}
												<li data-block="{{url}}">{% if $title is iterable %}{{$title.title}}{% else %}{{$title}}{% endif %}</li>
											{% endfor %}
										</ul>
									</div>
								{% endfor %} --}}
							</nav>
						</div>
						@endif
						
						<div class="header__logout ml4px noselect" logout>
							<i class="fa-solid fa-fw fa-arrow-right-from-bracket"></i>
							{{-- <span>{{__('auth.logout')}}</span> --}}
						</div>
					</div>
				</div>
			</div>
		</div>
	</header>
	
	<div class="content">
		{{-- <aside class="aside noselect">
			
		</aside> --}}
		
		<main class="main">
			<nav class="main__nav w25rem" id="mainNav">
				@isset($nav)
					{{-- <p>sectionTitle</p> --}}
					<ul class="noselect">
						@foreach($nav as $item)
							@if (!isset($item['section']))
								@continue
							@endif
							
							<li @class([
									'active' => $activeNav == $item['section'],
									'opened' => isset($item['active']),
								])
								loadsection="{{$item['section']}}"
								onclick="$.clickToNavItem(event)" 
								><span>{{$item['title']}}</span></li>	
								
						@endforeach
						
						{{-- <li class="line"></li>
						
						<teleport id="menuTeleport"></teleport> --}}
					</ul>
				@endisset
			</nav>
			<div id="sectionPlace" class="main__content"></div>
			
			{{-- <div class="footer">
				<div class="row g-10">
					<div class="col-auto">
						<div class="footer__block">
							<p>BPM systems {{date('Y')}} г.</p>
						</div>
					</div>
				</div>
			</div> --}}
		</main>
	</div>
	
</div>



@push('scripts')
<script type="module">
	let loadSectionController,
		uriSegment = getUrlSegment(0),
		startPage = '{{$site_start_page ?? 'common'}}',
		teleportEements = [];
	
	loadSection(uriSegment);
	
	$('[loadsection]').on(tapEvent, function(e) {
		if (!$(this).hasClass('active')) {
			if (loadSectionController && 'abort' in loadSectionController) loadSectionController.abort();
			let section = $(this).attr('loadsection');
			$('[loadsection]').not(this).removeClass('active');
			$(this).addClass('active');
			loadSection(section);
		}	
	});
	
	
	
	
	
	$('body').on(tapEvent, '[logout]', function() {
		axios.get('/logout', {
			responseType: 'json'
		}).then(function ({data, status, statusText, headers, config}) {
			if (data.logout) pageReload();	
		});
	});
	
	
	
	
	
	
	
	
	$.mainNavHendler = (event) => {
		event.stopPropagation();
		if (!$('#mainNav').hasClass('main__nav_visible')) {
			$('#mainNav').addClass('main__nav_visible');
			$('body').addClass('main__nav_opened');
		} else {
			$('#mainNav').removeClass('main__nav_visible');
		}
	}
	
	
	$(document).on(tapEvent, 'body.main__nav_opened', function() {
		$('#mainNav').removeClass('main__nav_visible');
		$('body.main__nav_opened').removeClass('main__nav_opened');
	});
	
	
	
	$.clickToNavItem = (event) => {
		event.stopPropagation();
		setTimeout(() => {
			$('#mainNav').removeClass('main__nav_visible');
		}, 100);
	}
	
	
	
	
	

	
	// Повторно отправить ссылку на подтверждение аккаунта
	$('body').on(tapEvent, '#resendVerifyLinkBtn', function() {
		let btn = $(this).closest('.button');
		let ddrWait = $(btn).ddrWait({iconHeight: '25px', backgroundColor: '#fffc', iconColor: 'hue-rotate(170deg)'});
		
		$.ddrFormSubmit({
			url: '/email/verification-notification',
			callback({sending = null, errors = null, message = null, status = null}, stat, headers) {
				
				if (sending) {
					$.notify(sending);
				}
				
				if (message) {
					$.notify(message, 'error');
				}
				
				ddrWait.destroy();
			},
			fail(data, status) {
				console.log(data, status);
				ddrWait.destroy();
			}
		});
	});
	
	
	
	
	
	
	
	
	
	
	
	
	//-----------------------------------------------------------------------------------------------------
	
	
	
	function loadSection(section = null) {
		$('#sectionPlace.main__content_visible').removeClass('main__content_visible');
		//$('#sectionTitle.header__pagetitle_visible').removeClass('header__pagetitle_visible');
		
		let loadSectionWait = $('#sectionPlace').ddrWait({
				iconHeight: '80px',
				backgroundColor: '#eceff3e6',
				iconColor: 'hue-rotate(147deg)',
				text: 'Загрузка...',
				fontSize: '18px'
			});
			
		loadSectionController = new AbortController();
		
		history.pushState(null, null, (section ? '/'+section : ''));
		
		//console.log(history.state['section']);
		//history.replaceState({page: 3}, "title 3", "?page=3")
		
		section = section || startPage;
		
		let getSection = axiosQuery('post', '/get_section', {section});
		
		closeNav();
		removeTeleports();
		
		getSection.then(function ({data, error, status, headers}) {
			if (error || status != 200) {
				if (error.message) $.notify(error.message, 'error');
				else $.notify('Ошибка загрузки раздела!', 'error');
				$('#sectionPlace').html('');
				$('#sectionTitle').text('');
				throw new Error('loadSection -> ошибка загрузки раздела!');
			} else {
				const dataDom = buildTeleports(data);
				$('#sectionPlace').html(dataDom);
				$('#sectionTitle').html(setPageTitle(headers['x-page-title']));
				$(document).trigger('onloadsection:site', section);
			}
			
			$('#sectionTitle:not(.header__pagetitle_visible)').addClass('header__pagetitle_visible');
			$('#sectionPlace:not(.main__content_visible)').addClass('main__content_visible');
			loadSectionWait.destroy();
			
			
		}).catch(err => {
			closeNav();
			
			if (axios.isCancel(err)) {
				console.log('Request canceled');
			} else {
				//$.notify('Ошибка загрузки раздела 3', 'error');
			}
			
			loadSectionWait.destroy();
		});
		
		
		
		// при переходе на другой раздел
		/*window.onpopstate = function(event) {
		  console.log(event.state);
		}*/
	}
	
	
	
	
	
	// Извлечь из HTML блоки для телепортации, вставить телепорты и вернуть HTML без телепортов
	function buildTeleports(data = null) {
		if (_.isNull(data)) return;
		let dataDom = $(data);
		let teleports = $(dataDom).find('[teleport]');
		if (teleports.length == 0) return data;
		
		$(dataDom).find('[teleport]').remove();
		
		const hasPleces = {};
		
		$.each(teleports, function(k, teleport) {
			let to = $(teleport).attr('teleport');
			
			if ($(to)[0] && teleport) {
				teleportEements.push({
					placement: $(to)[0].outerHTML,
					data: teleport
				});
			}
			
			$(teleport).removeAttrib('teleport');
			
			if (Object.keys(hasPleces).includes(to)) $(hasPleces[to]).after(teleport);
			else $(to).replaceWith(teleport);
			hasPleces[to] = teleport;
		});
		
		return dataDom;
	}
	
	
	function removeTeleports() {
		$.each(teleportEements, function(k, {placement, data}) {
			$(data).replaceWith(placement);
		});
		//$(teleportEements).remove();
		teleportEements = [];
		//
		//$(document).find('[teleport]').remove();
	}
	
	
	function getUrlSegment(index = 0) {
		let segments = location.pathname.substr(1).split('/');
		if (index == 'last') return segments.pop();
		if (segments[index] != 'undefined') return segments[index];
		return null;
	}
	
	
	
	function setPageTitle(titles = null) {
		if (!titles) return '';
		let titlesData = JSON.parse(titles);
		if (typeof titlesData == 'string') return JSON.parse(titles);
		let allTitlesString = '';
		titlesData.forEach((title, k) => {
			if (k+1 < titlesData.length) allTitlesString += '<span class="color-gray">'+title+' / </span>';
			else allTitlesString += '<span class="color-black">'+title+'</span>';
		});
		return allTitlesString;
	}
	
	
	
	function closeNav() {
		$('[headernav].header__nav_opened').removeClass('header__nav_opened');
		$('[touch="header__nav_opened"]').attr('aria-expanded', 'false');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	//-------------------------------------------------------------------------------- Общие настройки
	$.commonSettings = async () => {
		const {
			state, // isClosed
			wait,
			setTitle,
			setButtons,
			loadData,
			setHtml,
			setLHtml,
			dialog,
			close,
			query,
			onScroll,
			disableButtons,
			enableButtons,
			setWidth
		} = await ddrPopup({
			//url: 'site/contracts/settings',
			//method: 'get',
			//params: {setting: 'contracts'},
			title: 'Настройки',
			width: '600px', // ширина окна
			// frameOnly, // Загрузить только каркас
			// html, // контент
			// lhtml, // контент из языковых файлов
			// buttons, // массив кнопок
			// buttonsAlign, // выравнивание вправо
			// disabledButtons, // при старте все кнопки кроме закрытия будут disabled
			// closeByBackdrop, // Закрывать окно только по кнопкам [ddrpopupclose]
			// changeWidthAnimationDuration, // ms
			// buttonsGroup, // группа для кнопок
			// winClass, // добавить класс к модальному окну
			// centerMode, // контент по центру
			// topClose // верхняя кнопка закрыть
		})
		
		
		wait();
		
		const {data, error, status, headers} = await axiosQuery('get', 'site/contracts/settings', {setting: 'contracts'});
		
		setHtml(data);
		wait(false);	
	}
	
	
	
	
	
	$.setUserSetting = async (setting, inpType) => {
		const input = event.target;
		
		let value = null;
		
		switch (inpType) {
		  case 'checkbox':
			value = input?.checked || false;
			break;
		  
		  case 'text':
			value = input?.value || false;
			break;
		  
		  default:
			value = input?.checked || false;
			break;
		}
		
		$(input).ddrInputs('disable');
		
		const {data, error, status, headers} = await axiosQuery('post', 'site/contracts/settings', {setting, value});
		
		if (error || status != 200) {
			$.notify('Ошибка сохранения настройки!', 'error');
			console.error(error.message);
			return;
		}
		
		if (data) {
			$.notify('Сохранено!');
		}
		
		$(input).ddrInputs('enable');
	}
	
	
	
</script>
@endpush