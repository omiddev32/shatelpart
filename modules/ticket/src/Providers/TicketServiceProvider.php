<?php

namespace App\Ticket\Providers;

use App\Core\CoreServiceProvider as ServiceProvider;
use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Fields\{Text, Number};

class TicketServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = "ticket";

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageDisk('tickets');
    }

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerJsonTranslations();
        $this->registerWebRoute();
        $this->registerApiRoute();


        Nova::serving(function (){
            $moduleDir = base_path('modules');
            Nova::resources(getResources("{$moduleDir}/ticket/src/Resources" , "App/Ticket"));

            Nova::tools([
                (new \App\Ticket\Settings\TicketSettings())
            ]);

            Nova::addMenuIcons(__("Ticket Management"), 'inbox');

        });
        
        $this->registerViews();
        $this->registerMigrations();
    }
}
