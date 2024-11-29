<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeRepeatableCommand extends Command
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
    protected $signature = 'make:module:repeatable     
        {slug : The slug of the module.}
        {name : The name of the repeatable class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repeatable class';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('slug');
        $repeatable = $this->argument('name');
        $studlyModule = Str::studly($module);
        $repeaterName = Str::studly($repeatable);


        $pluralStudly = Str::snake(Str::pluralStudly($repeatable));
        $namespace = "App\\{$studlyModule}";
        $labelPluralStudly = Str::studly($pluralStudly);
        $entity = "$namespace\\Entities\\$repeaterName";
        $directionModule = base_path('modules') .'/' . $module . '/src';

        $template = str_replace([
            '{{repeaterName}}',
            '{{resourceLable}}',
            '{{namespace}}',
            '{{Entity}}',
        ],[
            $repeaterName,
            $labelPluralStudly,
            $namespace,
            $entity,
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Repeaters")) {
            $this->files->makeDirectory("{$directionModule}/Repeaters",0755, true);
        }

        file_put_contents("{$directionModule}/Repeaters/{$repeaterName}.php", $template);
        $this->info("Repeater $repeaterName has been created!");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/repeatable.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Repeatable name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}
