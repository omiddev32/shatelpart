<?php

namespace App\Payment\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;
use Illuminate\Console\Scheduling\Schedule;
use App\Payment\Commands\{AutomaticPaymentCancellationCommand};
use App\Payment\Services\ApiGatewayService;
use App\Payment\Facades\PaymentGateway;

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
        $this->app->alias(PaymentGateway::class, 'payment-gateway');
        $this->app->bind('payment-gateway' , function() {
            return new ApiGatewayService();
        });
        $this->commands([AutomaticPaymentCancellationCommand::class]);
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerApiRoute();
        $this->registerJsonTranslations();

        $this->app->booted(function() {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('payment:automatic-cancellation')
                ->everyMinute()
                ->runInBackground();
        });

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/payment/src/Resources" , "App/Payment"));
            Nova::addMenuIcons(__("Financial services and transactions"), 'cash');
        });

        $this->registerViews();
        $this->registerMigrations();
    }
}
