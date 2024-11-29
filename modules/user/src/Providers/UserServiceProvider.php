<?php

namespace App\User\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\User\Facades\Otp as OtpFacade;
use App\User\Services\OtpService;
use Laravel\Nova\Nova;
use Laravel\Nova\Menu\{Menu, MenuItem};
use Laravel\Nova\Fields\{Text, Number};
use App\User\Commands\DeleteExpiredOtpCodesCommand;
use Illuminate\Console\Scheduling\Schedule;

class UserServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "user";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(AuthServiceProvider::class);
        $this->registerStorageDisk('users');
        $this->registerStorageDisk('users.addresses');
        $this->registerStorageDisk('admins');
        // app('router')->aliasMiddleware('guestOrUser', \App\User\Http\Middleware\QuestOrUser::class);

        $this->app->alias(OtpFacade::class, 'otp-service');
        $this->app->bind('otp-service' , function() {
            return new OtpService();
        });

        $this->commands([DeleteExpiredOtpCodesCommand::class]);
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->registerWebRoute();
        $this->registerApiRoute();
        $this->registerJsonTranslations();

        $this->app->booted(function() {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('otp:delete-expired')
                ->everyMinute()
                ->runInBackground();
        });
        
        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/user/src/Resources" , "App/User"));

            Nova::tools([
                (new \App\User\Settings\UserProfile)
            ]);

            Nova::userMenu(function (Request $request, Menu $menu) {
                return $menu
                    ->prepend(MenuItem::link(__("Profile"), '/settings/user-profiles'));
                });

            Nova::addMenuIcons(__("Admins and Access"), 'user-group');
            Nova::addMenuIcons(__("Users"), 'users');
            Nova::addMenuIcons(__("Organization Management"), 'collection');
        });

        $this->registerViews();
        $this->registerMigrations();
    }
}
