<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeFilterCommand extends Command
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
    protected $signature = 'make:module:filter     
        {slug : The slug of the module.}
        {name : The name of the filter class.}';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module filter class.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('slug');
        $filter = $this->argument('name');
        $studlyModule = Str::studly($name);
        $namespace = "App\\{$studlyModule}";
        $directionModule = base_path('modules') .'/' . $name . '/src';
        $studlyName = Str::studly($filter);
        $template = str_replace([
            '{{studlyName}}',
            '{{namespace}}',
        ],[
            $studlyName,
            $namespace,
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Filters")) {
            $this->files->makeDirectory("{$directionModule}/Filters",0755, true);
        }
        file_put_contents("{$directionModule}/Filters/{$studlyName}.php" ,$template);

        $this->info("Filters $studlyName has been created!");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/filter.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Filter name'],
            ['module', InputArgument::REQUIRED, 'Module name']
        ];
    }
}