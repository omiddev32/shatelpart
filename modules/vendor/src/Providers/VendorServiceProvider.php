<?php

namespace App\Vendor\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Vendor\Commands\{GiftHubRefreshTokenCommand};
use App\Vendor\Facades\UpdateVendorBalance as UpdateVendorBalanceFacade;
use App\Vendor\Services\UpdateVendorBalanceService;
use Laravel\Nova\Nova;

class VendorServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "vendor";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([GiftHubRefreshTokenCommand::class]);

        $this->app->alias(UpdateVendorBalanceFacade::class, 'update-vendor-balance-service');
        $this->app->bind('update-vendor-balance-service' , function() {
            return new UpdateVendorBalanceService();
        });
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerWebRoute();
        $this->registerJsonTranslations();

        // $this->app->booted(function() {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('gifthub:refresh-token')
        //         ->everyThirtyMinutes()
        //         ->runInBackground();
        // });

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/vendor/src/Resources" , "App/Vendor"));
            Nova::addMenuIcons(__("Shop"), 'shopping-cart');
        });

        $this->registerMigrations();

    }
}
