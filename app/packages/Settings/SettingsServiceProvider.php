<?php

namespace App\Packages\Settings;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use App\Packages\Settings\Http\Middleware\AuthorizeSettings;

class SettingsServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->app->singleton("command.make.module.settings", function ($app) {
            return $app[\App\Packages\Settings\MakeSettingsCommand::class];
        });
        $this->commands("command.make.module.settings");

        // forResourceCreate

        // $this->app->singleton(Page::class, function () {
        //     return new NovaSettingsStore();
        // });
    }

    /**
     * Register page routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Nova::router()->group(function ($router) {
            $router->get("settings/{pageId?}", [\App\Packages\Settings\Http\Controllers\SettingsController::class, 'inertiaPage'])
                ->middleware(['nova', Authenticate::class])
                ->domain(config('nova.domain', null));
        });

        Route::middleware(['nova:api', AuthorizeSettings::class])
            ->domain(config('nova.domain', null))
            ->controller(\App\Packages\Settings\Http\Controllers\SettingsController::class)
            ->prefix('nova-api/settings')
            ->group(function() {
                Route::get('/{page}', 'loadPage');
                Route::post('/{page}', 'savePage');
            });

        // Route::namespace('\App\Packages\NovaPages\Http\Controllers')->group(function () {
        //     Route::prefix('nova-vendor/nova-settings')->group(function () {
        //         Route::get('/settings', 'SettingsController@get')->name('nova-settings.get');
        //         Route::post('/settings', 'SettingsController@save')->name('nova-settings.save');
        //     });

        //     Route::delete('/nova-api/nova-settings/{path}/field/{fieldName}', 'SettingsController@deleteImage');
        // });

            
    }
}
