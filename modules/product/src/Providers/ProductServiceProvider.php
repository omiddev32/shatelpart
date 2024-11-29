<?php

namespace App\Product\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use App\Product\Commands\{CysendFaceValueCommand, CysendProductsCommand};
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Nova\Nova;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "product";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageDisk('products');
        $this->registerStorageDisk('products.delivery-types');
        $this->registerStorageDisk('products.usings');
        $this->registerStorageDisk('products.types');
        $this->registerStorageDisk('products.introductions');
        $this->registerStorageDisk('products.applications');
        $this->registerStorageDisk('products.usage-methods');
        $this->registerStorageDisk('products.video-covers');
        $this->registerStorageDisk('products.email-contents');
        $this->commands([CysendFaceValueCommand::class, CysendProductsCommand::class]);

    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerJsonTranslations();
        $this->registerWebRoute();
        $this->registerApiRoute();

        $this->app->booted(function() {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('products:cysend')
                ->dailyAt('03:00')
                ->runInBackground();
            $schedule->command('faceValue:cysend')
                ->dailyAt('03:30')
                ->runInBackground();
        });

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/product/src/Resources" , "App/Product"));
        });

        $this->registerMigrations();
    }
}
