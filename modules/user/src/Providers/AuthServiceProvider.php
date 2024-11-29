<?php

namespace App\User\Providers;

use App\User\Entities\Admin;
use Illuminate\Support\Facades\Auth;
use App\User\{EloquentAdminProvider};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerAdminGuard();

        if (! $this->app->routesAreCached()) {
            // Passport::routes();
        }
    }

    /**
     * Register admin guard.
     *
     * @return void
     */
    private function registerAdminGuard()
    {
        Auth::provider('admin', function($app) {
            return new EloquentAdminProvider($app['hash'], Admin::class);
        });        
    }
}