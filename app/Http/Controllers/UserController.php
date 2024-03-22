<?php namespace App\Http\Controllers;

use App\Actions\AjaxDataAction;
use App\Actions\GetUserSetting;
use App\Enums\OrderColums;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class UserController extends Controller {
    
	/**
	 * @param 
	 * @return  
	 */
	public function authForm() {
		if (Auth::guard('site')->check()) return response()->json(['auth' => true]);
		session(['site-auth-view' => 'site.auth.auth']);
		$locale = App::currentLocale();
		return view('site.auth.auth', compact(['locale']));
	}
	
	/**
	 * @param 
	 * @return 
	 */
	public function login(Request $request) {
		$request->merge(['email' => encodeEmail($request->input('email'))]);
		
		$authFields = $request->validate([
			'email' 	=> 'required|email|exists:users,email',
			'password' 	=> 'required|string'
		]);
		
		if (!Auth::guard('site')->attempt($authFields, true)) return response()->json(['no_auth' => __('auth.failed')]);
		
		if (!Auth::guard('site')->user()->email_verified_at) {
			User::where('id', Auth::guard('site')->user()->id)->update(['email_verified_at' => Date::now()]);
		}
		
		$redirect = $request->session()->pull('auth_redirect', '/');
		$request->session()->regenerate();
		session(['site-login' => __('auth.auth_success')]);
		return response()->json(['redirect' => $redirect]);
	}
	
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function get_settings(GetUserSetting $getUserSetting) {
		if(!$user = Auth::guard('site')->user()) return response()->json(false);
		
		[
			'order_colums' => $userColumsSettings,
			'show_past_orders_in_actual' => $showPastOrdersInActual,
			'events_search_orders_in_period' => $eventsSearchOrdersInPeriod,
		] = $getUserSetting([
			'order_colums',
			'show_past_orders_in_actual',
			'events_search_orders_in_period',
		]);
		
		$orderColums = OrderColums::asFullArray();
		
		return view('site.render.settings', compact('orderColums', 'userColumsSettings', 'showPastOrdersInActual', 'eventsSearchOrdersInPeriod'));
	}
	
	
	
	/**
	* 
	* @param 
	* @return 
	*/
	public function set_setting(Request $request, AjaxDataAction $ajaxAction) {
		$fields = $request->validate([
			'setting'	=> 'required|string',
			'value'		=> 'nullable',
			'type'		=> 'required|string', // single arr
			'remove'	=> 'boolean',
		]);
		
		if(!$user = Auth::guard('site')->user()) return response()->json(false);
		
		$mutated = $ajaxAction($user->settings, $fields);
		
		$user->settings = $mutated;
		$saveRes = $user->save();
		return response()->json($saveRes);
	}
	
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	//public function regForm(Request $request) {
	//	session(['site-auth-view' => 'site.auth.reg']);
	//	$locale = App::currentLocale();
	//	return view('site.auth.reg', compact(['locale']));
	//}
	
	
	/**
	 * @param 
	 * @return 
	 */
	//public function register(RegRequest $req) {
	//	$validFields = $req->validated();
	//	if (!$user = User::create($validFields)) return response()->json(['reg' => __('auth.reg_failed')]);
	//	event(new Registered($user));
	//	Auth::guard('site')->login($user, true);
	//	session(['site-register' => __('auth.reg_success')]);
	//	session()->forget('site-auth-view');
	//	return response()->json(['reg' => __('auth.reg_success')]);
	//}
	
	
	
	
	public function logout(Request $request) {
		if (!Auth::guard('site')->check()) return response()->json(['no_auth' => true]);
	    $locale = $request->session()->pull('locale');
	    Auth::guard('site')->logout();
		//Auth::logoutOtherDevices($request->getPassword());
	    $request->session()->invalidate();
	    $request->session()->regenerateToken();
	    $request->session()->put('locale', $locale);
		return response()->json(['logout' => true]);
	}
}