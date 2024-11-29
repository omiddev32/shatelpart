<?php

namespace App\Fields\SelectPlus;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as CoreServiceProvider;

class ServiceProvider extends CoreServiceProvider
{
    public function boot()
    {
        Route::group(
            ['middleware' => 'nova', 'prefix' => 'nova-vendor/select-plus', 'namespace' => __NAMESPACE__],
            function ($route) {
                $route->get('/{resource}/{relationship}', 'Controller@options');
            }
        );
    }

    public function register()
    {
        //
    }
}
