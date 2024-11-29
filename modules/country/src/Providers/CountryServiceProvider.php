<?php

namespace App\Country\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;

class CountryServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "country";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageDisk('countries.1x1');
        $this->registerStorageDisk('countries.3x2');
        $this->registerStorageDisk('zones');
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerWebRoute();
        $this->registerApiRoute();
        $this->registerJsonTranslations();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/country/src/Resources" , "App/Country"));
        });

        $this->registerMigrations();
    }
}
