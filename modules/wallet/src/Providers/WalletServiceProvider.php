<?php

namespace App\Wallet\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "wallet";

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
        $this->registerApiRoute();
        $this->registerJsonTranslations();
    }
}
