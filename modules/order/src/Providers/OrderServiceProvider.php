<?php

namespace App\Order\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Order\Commands\{DeleteExpiredOrdersCommand};
use Laravel\Nova\Nova;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "order";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([DeleteExpiredOrdersCommand::class]);
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerJsonTranslations();
        $this->registerApiRoute();

        $this->app->booted(function() {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('orders:delete-expired')
                ->everyMinute()
                ->runInBackground();
        });

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/order/src/Resources" , "App/Order"));

            Nova::tools([
                (new \App\Order\Settings\OrdersSettings())
            ]);
            Nova::addMenuIcons(__("Orders"), 'template');
        });

        $this->registerViews();
        $this->registerMigrations();
    }
}
