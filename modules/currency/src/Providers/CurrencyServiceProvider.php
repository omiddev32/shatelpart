<?php

namespace App\Currency\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Currency\Commands\UpdateCurrencyPriceCommand;
use Laravel\Nova\Nova;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "currency";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([UpdateCurrencyPriceCommand::class]);
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


        $this->app->booted(function() {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('currencies:update')
                ->everyMinute()
                ->runInBackground();

        });

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/currency/src/Resources" , "App/Currency"));
        });

        $this->registerMigrations();
    }
}
