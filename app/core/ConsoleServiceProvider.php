<?php

namespace App\Core;

use Illuminate\Support\ServiceProvider;


class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the provided services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the provided services.
     */
    public function register()
    {
        // $this->registerDisableCommand();
        // $this->registerEnableCommand();
        $this->registerListCommand();
        // $this->registerMigrateCommand();
        // $this->registerMigrateRefreshCommand();
        // $this->registerMigrateResetCommand();
        // $this->registerMigrateRollbackCommand();
        $this->registerOptimizeCommand();
        // $this->registerSeedCommand();
        $this->migrationDataCommand();
        // $this->registerEnvironmentTypeCommand();
    }

    /**
     * Register the module:enable command.
     */
    protected function registerEnableCommand()
    {
        $this->app->singleton('command.module.enable', function () {
            return new \App\Core\Console\Commands\ModuleEnableCommand();
        });

        $this->commands('command.module.enable');
    }
    
    /**
     * Register the module:disable command.
     */
    protected function registerDisableCommand()
    {
        $this->app->singleton('command.module.disable', function () {
            return new \App\Core\Console\Commands\ModuleDisableCommand();
        });

        $this->commands('command.module.disable');
    }

    /**
     * Register the module:list command.
     */
    protected function registerListCommand()
    {
        $this->app->singleton('command.module.list', function ($app) {
            return new \App\Core\Console\Commands\ModuleListCommand($app['modules']);
        });

        $this->commands('command.module.list');
    }

    /**
     * Register the module:migrate command.
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.module.migrate', function ($app) {
            return new \App\Core\Console\Commands\ModuleMigrateCommand($app['migrator'], $app['modules']);
        });

        $this->commands('command.module.migrate');
    }

    /**
     * Register the module:migrate:refresh command.
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.module.migrate.refresh', function () {
            return new \App\Core\Console\Commands\ModuleMigrateRefreshCommand();
        });

        $this->commands('command.module.migrate.refresh');
    }

    /**
     * Register the module:migrate:reset command.
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.module.migrate.reset', function ($app) {
            return new \App\Core\Console\Commands\ModuleMigrateResetCommand($app['modules'], $app['files'], $app['migrator']);
        });

        $this->commands('command.module.migrate.reset');
    }

    /**
     * Register the module:migrate:rollback command.
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.module.migrate.rollback', function ($app) {
            $repository = $app['migration.repository'];
            $table = $app['config']['database.migrations'];

            $migrator = new Migrator($table, $repository, $app['db'], $app['files']);

            return new \App\Core\Console\Commands\ModuleMigrateRollbackCommand($migrator, $app['modules']);
        });

        $this->commands('command.module.migrate.rollback');
    }

    /**
     * Register the module:optimize command.
     */
    protected function registerOptimizeCommand()
    {
        $this->app->singleton('command.module.optimize', function () {
            return new \App\Core\Console\Commands\ModuleOptimizeCommand();
        });

        $this->commands('command.module.optimize');
    }
    
    /**
     * Register the module:seed command.
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.module.seed', function ($app) {
            return new \App\Core\Console\Commands\ModuleSeedCommand($app['modules']);
        });

        $this->commands('command.module.seed');
    }
    
    /**
     * Register the migrate:data.
     */
    protected function migrationDataCommand()
    {
        $this->app->singleton('command.migrate.data', function ($app) {
            return new \App\Core\Console\Commands\MigrationDataCommand($app['modules']);
        });

        $this->commands('command.migrate.data');
    }
        
    /**
     * Register the data:check.
     */
    protected function registerEnvironmentTypeCommand()
    {
        $this->app->singleton('command.env.mode', function ($app) {
            return new \App\Core\Console\Commands\EnvironmentType();
        });

        $this->commands('command.env.mode');
    }
}
