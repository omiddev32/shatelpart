<?php

namespace Laravel\Nova;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Logout;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Auth\Adapters\SessionImpersonator;
use Laravel\Nova\Contracts\ImpersonatesUsers;
use Laravel\Nova\Contracts\QueryBuilder;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Http\Middleware\ServeNova;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Listeners\BootNova;
use Laravel\Nova\Query\Builder;
use Laravel\Octane\Events\RequestReceived;
use Spatie\Once\Cache;

/**
 * The primary purpose of this service provider is to push the ServeNova
 * middleware onto the middleware stack so we only need to register a
 * minimum number of resources for all other incoming app requests.
 */
class NovaCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::booted(BootNova::class);

        if ($this->app->runningInConsole()) {
            $this->app->register(NovaServiceProvider::class);
        }

        Route::middlewareGroup('nova', config('nova.middleware', []));
        Route::middlewareGroup('nova:api', config('nova.api_middleware', []));

        $this->app->make(HttpKernel::class)
                    ->pushMiddleware(ServeNova::class);

        $this->app->afterResolving(NovaRequest::class, function ($request, $app) {
            if (! $app->bound(NovaRequest::class)) {
                $app->instance(NovaRequest::class, $request);
            }
        });

        $this->registerEvents();
        $this->registerJsonVariables();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('NOVA_PATH')) {
            define('NOVA_PATH', realpath(__DIR__.'/./'));
        }

        $this->app->singleton(ImpersonatesUsers::class, SessionImpersonator::class);

        $this->app->bind(QueryBuilder::class, function ($app, $parameters) {
            return new Builder(...$parameters);
        });
    }

    /**
     * Register the package events.
     *
     * @return void
     */
    protected function registerEvents()
    {
        tap($this->app['events'], function ($event) {
            $event->listen(Attempting::class, function () {
                app(ImpersonatesUsers::class)->flushImpersonationData(request());
            });

            $event->listen(Logout::class, function () {
                app(ImpersonatesUsers::class)->flushImpersonationData(request());
            });

            $event->listen(RequestReceived::class, function ($event) {
                Nova::flushState();
                /** @phpstan-ignore-next-line */
                Cache::getInstance()->flush();

                $event->sandbox->forgetInstance(ImpersonatesUsers::class);
            });

            $event->listen(RequestHandled::class, function ($event) {
                Container::getInstance()->forgetInstance(NovaRequest::class);
            });
        });
    }

    /**
     * Register the Nova JSON variables.
     *
     * @return void
     */
    protected function registerJsonVariables()
    {
        $languages = [];

        if (app()->environment(['local','development', 'production'])) { 
            $languages[] = app_path('languages/'.app()->getLocale().'.json');
    
            foreach (app('modules')->enabled()->sortBy(['order']) as $module) {
                $moduleName = $module['slug'];
                $moduleDir  = base_path('modules');
                $lang = app()->getLocale();
    
                if (is_dir("{$moduleDir}/{$moduleName}/languages")) {
                    if (is_file("{$moduleDir}/{$moduleName}/languages/{$lang}.json")) {
                        $languages[] = "{$moduleDir}/{$moduleName}/languages/{$lang}.json";
                    }
                }
            }
        }

        // dd($languages);

        Nova::serving(function (ServingNova $event) use($languages) {
            // Load the default Nova translations.
            Nova::translations($languages);

            // dd(Nova::allTranslations());

            Nova::provideToScript([
                'appName' => Nova::name() ?? config('app.name', 'Laravel Nova'),
                'timezone' => config('app.timezone', 'UTC'),
                'translations' => function () {
                    return Nova::allTranslations();
                },
                'userTimezone' => function ($request) {
                    return Nova::resolveUserTimezone($request);
                },
                'pagination' => config('nova.pagination', 'links'),
                'locale' => config('app.locale', 'en'),
                'algoliaAppId' => config('services.algolia.appId'),
                'algoliaApiKey' => config('services.algolia.apiKey'),
            ]);
        });
    }
}
