<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeFieldCommand extends Command
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
    protected $signature = 'make:module:field     
        {slug : The slug of the module.}
        {name : The name of the field class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module field class.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $module = $this->argument('slug');
        $studlyName = Str::studly($name);
        $directionModule = base_path('modules') .'/' . $module . '/src';
        $studlyModule = Str::studly($module);
        $namespace = "App\\{$studlyModule}";
        $uriKey = Str::kebab(preg_replace('/[^a-zA-Z0-9]+/', '', $this->argument('name')));
        $template = str_replace([
            '{{studlyName}}',
            '{{namespace}}',
            '{{uriKey}}'
        ],[
            $studlyName,
            $namespace,
            $uriKey
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Fields")) {
            $this->files->makeDirectory("{$directionModule}/Fields",0755, true);
        }

        file_put_contents("{$directionModule}/Fields/{$studlyName}.php" ,$template);
        $this->info("Field $studlyName has been created!");

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/field.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Field name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}