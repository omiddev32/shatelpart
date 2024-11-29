<?php

namespace App\Fields;

use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Register fields service provider.
     *
     * @return void
     */
    protected function fields()
    {
        return [
            "SelectPlus", "Translatable"
        ];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        foreach($this->fields() as $directory):
            $this->app->register("App\\Fields\\{$directory}\\ServiceProvider");
        endforeach;
    }

    /**
     * Bootstrap the field services.
     *
     * @return void
     */
    public function boot()
    {
        // 
    }
}
