<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
		$guard = request()->segment(1) == 'admin' ? 'admin' : 'site';
		Broadcast::routes(['middleware' => $guard]);

        require base_path('routes/channels.php');
    }
}
