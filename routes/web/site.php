<?php

use App\Http\Controllers\Business\OrdersController;
use App\Http\Controllers\SlackController;
use App\Http\Controllers\UserController;
use App\Http\Requests\Auth\UserEmailVerificationRequest;
use App\Models\Section;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;








Route::post('/preg', function(Request $request) {
	logger('dfgdfgdf');
	
	return response()->json(['foo' => 'bar']);
	
	
	/* $dt = Carbon::create(1975, 12, 25, 14, 15, 16);
	
	echo '<pre>';
		print_r($dt->toDayDateTimeString());
	exit('</pre>'); */
	
	/* $str = '===
WoW Retail, Vault of the Incarnates Heroic Raid Boost, (us-servers) (selfplayed) (standard-8of8-bosses) (Fury Warrior), (Fri 21 Apr @ 09:30 AM EDT), Lardug, Area 52, Horde, DizzyDwarf#1210527, &A1102B
WoW Retail, Vault of the Incarnates Heroic Raid Boost, (us-serverss) (selfplayed) (standard-8of8-bosses) (Fury Warrior), (Fri 21 Apr @ 09:30 AM EDT), Lardug, Area 52, Horde, DizzyDwarf#1210527, &A1102B
https://worldofwarcraft.com/en-us/character/us/area52/Lardug
===';
	$res = $order->parse($str);
	
	
	Order::insert($res);
	
	echo '<pre>';
		print_r($res);
	echo '</pre>'; */


});




// регистрация, авторизация, выход
Route::controller(UserController::class)->middleware(['lang', 'isajax:site'])->group(function() {
	//Route::get('/reg', 'regForm')->name('site.reg');
	//Route::post('/register', 'register');
	Route::get('/auth', 'authForm')->name('site.auth');
	Route::post('/login', 'login');
	Route::get('/logout', 'logout')->name('site.logout');
});



// подтверждение адреса почты
/* Route::get('/email/verify', function () {
    return view('site.auth.verify-email');
})->middleware('auth:site')->name('site.verification.notice'); */

Route::get('/email/verify/{id}/{hash}', function (UserEmailVerificationRequest $request) {
	$request->fulfill();
	session(['site-email-verified' => __('auth.email_verified')]);
	return redirect(route('site'));
})->middleware(['lang', 'auth:site', 'signed'])->name('site.verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification('site');
    return response()->json(['sending' => __('auth.email_verify_sending')]);
})->middleware(['lang', 'auth:site', 'throttle:6,1'])->name('site.verification.send');




// Сброс пароля
Route::get('/forgot-password', function (Request $request) {
	$email = encodeEmail($request->input('email'));
	return view('site.auth.forgot-password', ['email' => $email]);
})->middleware('lang', 'guest:site')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
	$request->merge(['email' => encodeEmail($request->input('email'))]);
	$request->validate(['email' => 'required|email|exists:users,email']);
    $status = Password::broker('users')->sendResetLink($request->only('email'), function($user, $token) {
		$user->sendPasswordResetNotification($token, 'site');
	});
	
	if ($status === Password::RESET_LINK_SENT) {
		return response()->json(['message' => __($status)]);
	} else {
		return response()->json(['errors' => ['email' => [__($status)]]]);
	}
})->middleware(['lang', 'guest:site'])->name('site.password.email');

Route::get('/reset-password/{token}', function ($token, Request $request) {
    return view('site.index', ['reset' => true, 'token' => $token, 'email' => encodeEmail($request->email)]);
})->middleware(['lang', 'guest:site'])->name('site.password.reset');

Route::post('/reset-password', function (Request $request) {
	$request->validate([
        'token' => 'required',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:8|confirmed',
    ]);
	
	$status = Password::broker('users')->reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => $password
            ])->setRememberToken(Str::random(60));

            $user->save();
            event(new PasswordReset($user));
        }
    );
	
	if ($status === Password::PASSWORD_RESET) {
		session(['site-reset-password' => __($status)]);
		return response()->json(['redirect' => route('site')]);
	} else {
		return response()->json(['errors' => ['email' => __($status)]]);
	}
})->middleware(['lang', 'guest:site'])->name('password.update');









// тест перечисления enum Period
/* Route::get('test', function() {
	$status = Period::tryFrom(request('period'));
	
	if ($status) dd($status->date()->locale('ru')->isoFormat('DD MMMM YYYY', 'Do MMMM'));
	else echo 'no';
}); */








//--------------------------------------------------------------------------------------------





// сайт prefix('site') уже подключен
Route::middleware(['lang'])->get('/{section?}', function (Request $request, $section = null) {
	if (!Auth::guard('site')->check() && $section) return redirect()->route('site');
	
	$settingsService = App::make(Settings::class);
	$settings = $settingsService->getMany('company_name', 'site_start_page', 'show_nav', 'show_locale'); // прописать настройки для вывода в общий шаблон личного кабинета
	
	$activeNav = $section ?: ($settings['site_start_page'] ?? 'common');
	
	$locale = App::currentLocale();
	
	$nav = auth('site')->check() ? (new Section)->getSections($activeNav) : [];
	
	$user = Auth::guard('site')->user();
	
	return view('site.index', compact('locale', 'user', 'nav', 'activeNav'), $settings->all());
})->name('site');






// Получить данные раздела
Route::middleware(['lang', 'auth:site', 'isajax:site'])->post('/get_section', function (Request $request, Settings $settings) {
	$section = $request->input('section');
	$pageTitle = [];
	
	if (!Section::where('section', $section)->count()) {
		return response()
				->view('site.section.error', ['title' => __('custom.no_section_title'), 'message' => __('custom.no_section_message')], 200)
				->header('X-Page-Title', '');
	}
	
	if ($request->user('site')->cannot('section-'.$section.':site')) {
		return response()
			->view('site.section.denied', ['title' => __('custom.denied_section_title'), 'message' => __('custom.denied_section_message')], 200)
			->header('X-Page-Title', ''/* urlencode(__('custom.denied_section_header_title')) */);
	}
	
	
	$sectionPath = $section;
	
	$rootSection = explode('.', $sectionPath);
	if (count($rootSection) > 1) {
		$pageData = Section::select('page_title')
			->where('section', $rootSection)->first();
		$pageTitle[] = $pageData['page_title'];
	}
	
	if (!View::exists('site.section.'.$section)) {
		$sectionPath = match (true) {
			View::exists('site.section.'.$section.'.index') => $section.'.index',
			View::exists('site.section.'.$section.'.default') => $section.'.default',
			View::exists('site.section.'.$section.'.'.$section) => $section.'.'.$section,
			default => false
		};
		
		if (!$sectionPath) {
			return response()
				->view('site.section.error', ['title' => __('custom.no_section_title'), 'message' => __('custom.no_section_message')], 200)
				->header('X-Page-Title', ''/* urlencode(__('custom.no_section_header_title')) */);
		}
	} 
	
	
	$page = Section::select('page_title', 'settings')
		->where('section', str_replace([
			'.index','.default',$section.'.'.$section],
			['','',$section],
			$sectionPath))
		->first();
	
	// в таблице sections прописывается массив тех настроек, что нужно подгрузить
	$settingsData = $page['settings'] ? ($settings->getMany($page['settings'])->toArray() ?: []) : []; 
	
	
	$pageTitle[] = $page ? $page->page_title : null; /* urlencode(__('custom.no_section_header_title')) */
	
	$user = Auth::guard('site')->user();
	
	// Сюда добавляюся любые данные пользователя
	$data = [
		'user' 			=> $user,
		'setting' 		=> $settingsData
	];
	
	
	return response()->view('site.section.'.$sectionPath, $data/* сюда данные */, 200)->header('X-Page-Title', json_encode($pageTitle));
});













Route::post('/lang', function (Request $request) {
	$locale = $request->input('locale');
	if (!$locale) return response()->json(['no_locale_send' => true]);
	$locales = config('app.locales_list');
	if (!$locales) return response()->json(['no_locales' => true]);
	if (!in_array($locale, $locales)) return response()->json(['locale_not_exists' => true]);
	
	Session::put('locale', $locale);
	if (Auth::guard('site')->check()) {
		User::where('id', $request->user('site')->id)->update(['locale' => $locale]);
	} else {
		if (!Session::has('locale')) {
			Session::put('locale', config('app.locale'));
		}
	}
    
	App::setLocale($locale);
	return response()->json(['set_locale' => true]);
})->middleware(['isajax:site']);










//-------------------------------------------------------------------------------------------------- Slack
Route::controller(SlackController::class)->prefix('slack')/* ->middleware(['lang', 'isajax']) */->group(function() {
	Route::post('/incoming_order', 'incomingOrder'); // Прослушки новых сообщений !!! app\Http\Middleware\VerifyCsrfToken.php добавить !!!
	Route::post('/send_message', 'send_message'); // Отправить сообщение
});













//---------------------------------------------------------------------------------------------------- РОУТЫ (с префиксом client)

Route::prefix('client')->middleware(['lang', 'isajax:site'])->group(function() {
	Route::get('/orders', [OrdersController::class, 'list']);
	Route::post('/orders/incoming_orders', [OrdersController::class, 'incoming_orders']);
	Route::post('/orders/to_wait_list', [OrdersController::class, 'to_wait_list']);
	Route::post('/orders/to_cancel_list', [OrdersController::class, 'to_cancel_list']);
	
	Route::get('orders/to_wait_list', [OrdersController::class, 'to_wait_list_form']);
	Route::get('orders/to_cancel_list', [OrdersController::class, 'to_cancel_list_form']);
	Route::get('orders/relocate', [OrdersController::class, 'relocate_client']);
	Route::get('orders/relocate/get_timesheets', [OrdersController::class, 'get_relocate_timesheets_client']);
	Route::post('orders/relocate', [OrdersController::class, 'set_relocate_client']);
});














Route::fallback(function () {
    return;
});
























// Route::post('/agreement', function (/*Request $request, Rool $rool*/) {
// 	//$ttt = $rool->bar();
// 	//$foo = $request->input('foo');
// 	//echo '<h1">'.$ttt.' '.$foo.'</h1>';
// 	
// 	return '<p>Настоящее Соглашение с Пользователем, регламентирует условия использования Сервиса, а
// 		также права и обязанности Пользователя и Администрации Сервиса.</p><p>
// 		Настоящее Соглашение заключается между Пользователем и Администрацией Сервиса и
// 		является публичной офертой в соответствии со ст. 437 Гражданско</p>';
// });


