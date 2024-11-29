<?php

namespace App\Core;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the provided services.
     */
    public function register()
    {
        $generators = [
            'module' => \App\Core\Console\Generators\MakeModuleCommand::class,
            'entity' => \App\Core\Console\Generators\MakeEntityCommand::class,
            'controller' => \App\Core\Console\Generators\MakeControllerCommand::class,
            'action' => \App\Core\Console\Generators\MakeActionCommand::class,
            'card' => \App\Core\Console\Generators\MakeCardCommand::class,
            'dashboard' => \App\Core\Console\Generators\MakeDashboardCommand::class,
            'event' => \App\Core\Console\Generators\MakeEventCommand::class,
            'field' => \App\Core\Console\Generators\MakeFieldCommand::class,
            'filter' => \App\Core\Console\Generators\MakeFilterCommand::class,
            'job' => \App\Core\Console\Generators\MakeJobCommand::class,
            'job' => \App\Core\Console\Generators\MakeJobCommand::class,
            'listener' => \App\Core\Console\Generators\MakeListenerCommand::class,
            'mail' => \App\Core\Console\Generators\MakeMailCommand::class,
            'middleware' => \App\Core\Console\Generators\MakeMiddlewareCommand::class,
            'migration' => \App\Core\Console\Generators\MakeMigrationCommand::class,
            'notification' => \App\Core\Console\Generators\MakeNotificationCommand::class,
            'observer' => \App\Core\Console\Generators\MakeObserverCommand::class,
            'partition' => \App\Core\Console\Generators\MakePartitionCommand::class,
            'policy' => \App\Core\Console\Generators\MakePolicyCommand::class,
            'provider' => \App\Core\Console\Generators\MakeProviderCommand::class,
            'request' => \App\Core\Console\Generators\MakeRequestCommand::class,
            'resource' => \App\Core\Console\Generators\MakeResourceCommand::class,
            'value' => \App\Core\Console\Generators\MakeValueCommand::class,
            'route' => \App\Core\Console\Generators\MakeRouteCommand::class,
            'language' => \App\Core\Console\Generators\MakeLanguageCommand::class,
            'menu' => \App\Core\Console\Generators\MakeMenuCommand::class,
            'data' => \App\Core\Console\Generators\MakeDataCommand::class,
            'config' => \App\Core\Console\Generators\MakeConfigCommand::class,
            'repeatable' => \App\Core\Console\Generators\MakeRepeatableCommand::class,
        ];

        $this->registerCommands($generators);
    }

    /*
     * @params array $generators
     *
     * @return void
     */
    public function registerCommands(array $generators)
    {
        foreach ($generators as $commandName => $class) :

            if ($commandName == 'module') :
                $slug = "command.make.module";
            else:
                $slug = "command.make.module.{$commandName}";
            endif;

            $this->app->singleton($slug, function ($app) use ($class) {
                return $app[$class];
            });
            $this->commands($slug);

        endforeach;
    }
}
