<?php

namespace Laravel\Nova;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class NovaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCarbonMacros();
        $this->registerCollectionMacros();
        $this->registerRelationsMacros();
    }


    /**
     * Register the Nova Carbon macros.
     *
     * @return void
     */
    protected function registerCarbonMacros()
    {
        Carbon::mixin(new Macros\FirstDayOfQuarter);
        Carbon::mixin(new Macros\FirstDayOfPreviousQuarter);
        CarbonImmutable::mixin(new Macros\FirstDayOfQuarter);
        CarbonImmutable::mixin(new Macros\FirstDayOfPreviousQuarter);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register Collection macros.
     *
     * @return void
     */
    protected function registerCollectionMacros()
    {
        Collection::macro('isAssoc', function () {
            /** @phpstan-ignore-next-line */
            return Arr::isAssoc($this->toBase()->all());
        });
    }

    /**
     * Register Relations macros.
     *
     * @return void
     */
    protected function registerRelationsMacros()
    {
        BelongsToMany::mixin(new Query\Mixin\BelongsToMany());
    }
}
