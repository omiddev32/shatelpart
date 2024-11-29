<?php

namespace App\Category\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;

class CategoryServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "category";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageDisk('categories');
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerApiRoute();
        $this->registerJsonTranslations();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/category/src/Resources" , "App/Category"));
        });

        $this->registerMigrations();
    }
}
