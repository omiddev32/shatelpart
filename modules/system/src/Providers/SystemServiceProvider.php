<?php

namespace App\System\Providers;

use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Schema};
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Core\CoreServiceProvider as ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "system";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(NovaServiceProvider::class);
        // $this->app->register(BroadcastServiceProvider::class);
        // $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->registerWebRoute(false);
        $this->registerCustomRoute('nova');
        $this->registerNovaRoutes();
        $this->registerJsonTranslations();
        $this->registerViews();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/system/src/Resources" , "App/System"));
            // Nova::script($this->moduleName , asset("panel/modules/{$this->moduleName}.js"));
            Nova::addMenuIcons(__("Logs"), 'document-report');
            Nova::addMenuIcons(__("General Settings"), 'cog');
        });
        

        $this->registerMigrations();
    }

    /**
     * Register the resource manager routes.
     *
     * @return void
     */
    protected function registerNovaRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/nova-api.php');
        });
    }

    /**
     * Get the nova route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'domain' => config('nova.domain', null),
            'as' => 'nova.api.',
            'prefix' => 'nova-api',
            'middleware' => 'nova:api',
            'excluded_middleware' => [SubstituteBindings::class],
        ];
    }
}
