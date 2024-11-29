<?php

namespace App\Payment\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;
use App\Payment\Gateway;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "payment";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('PaymentGateway', function () {
            return new Gateway();
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
        $this->registerJsonTranslations();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/payment/src/Resources" , "App/Payment"));
            Nova::addMenuIcons(__("Financial services and transactions"), 'cash');
        });

        $this->registerViews();

        $this->registerMigrations();
    }
}
