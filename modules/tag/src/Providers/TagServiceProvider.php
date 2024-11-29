<?php

namespace App\Tag\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;

class TagServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "tag";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerJsonTranslations();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/tag/src/Resources" , "App/Tag"));
        });

        $this->registerMigrations();
    }
}
