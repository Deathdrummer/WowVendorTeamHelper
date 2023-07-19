<?php namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string {
        session(['auth_redirect' => URL::current()]);
        if (!$request->expectsJson()) {
			return $request->is('admin/*') ? route('admin') : route('site');
        } else {
			return response()->json(['error' => 401, 'auth' => false]);
		}
    }
}
