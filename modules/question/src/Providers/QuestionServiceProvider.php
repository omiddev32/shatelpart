<?php

namespace App\Question\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;

class QuestionServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "question";

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
        $this->registerJsonTranslations();

        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/question/src/Resources" , "App/Question"));
            Nova::addMenuIcons(__("FAQ"), 'question-mark-circle');
        });

        $this->registerMigrations();
    }
}
