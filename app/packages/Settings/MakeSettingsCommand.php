<?php

namespace App\Packages\Settings;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Filesystem\Filesystem;

class MakeSettingsCommand extends Command
{

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module:settings     
        {slug : The slug of the module.}
        {name : The name of the settings class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module settings class.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $module = $this->argument('slug');
        $studlySettings = Str::studly($name);
        $settingsPermission = Str::snake(Str::pluralStudly($name));
        $directionModule = base_path('modules') .'/' . $module . '/src';
        $studlyModule = Str::studly($module);
        $namespace = "App\\{$studlyModule}";
        $settingsModel = "\\$namespace\\Entities\\$studlySettings::class";

        $template = str_replace([
            '{{settingsName}}',
            '{{namespace}}',
            '{{settingsModel}}',
            '{{settingsPermission}}'
        ],[
            $studlySettings,
            $namespace,
            $settingsModel,
            $settingsPermission
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Settings")) {
            $this->files->makeDirectory("{$directionModule}/Settings",0755, true);
        }

        file_put_contents("{$directionModule}/Settings/{$studlySettings}.php" ,$template);

        $this->info("Settings $studlySettings has been created!");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('packages/Settings/settings.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Settings name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}