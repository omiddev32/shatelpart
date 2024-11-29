<?php

namespace App\Message\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use App\Message\Facades\Message as MessageFacade;
use App\Message\Services\MessageGatewayService;

class MessageServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "message";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias(MessageFacade::class, 'message-service');
        $this->app->bind('message-service' , function() {
            return new MessageGatewayService();
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
        $this->registerApiRoute();
    }
}
