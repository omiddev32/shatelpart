<?php

namespace App\System\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Broadcast::routes(['middleware' => 'nova']);

        // require module_path('system') . 'routes/channels.php';

        // require base_path('routes/channels.php');
    }
}
