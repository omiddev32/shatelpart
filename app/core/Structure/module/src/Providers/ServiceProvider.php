<?php

namespace DummyNamespace\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;

class DummyProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "DummySlug";

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
        //
    }
}
