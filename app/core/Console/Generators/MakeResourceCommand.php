<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeResourceCommand extends Command
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
    protected $signature = 'make:module:resource     
        {slug : The slug of the module.}
        {name : The name of the resource class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module resource class';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('slug');
        $resource = $this->argument('name');
        $studlyModule = Str::studly($module);
        $studlyResource = Str::studly($resource);


        $pluralStudly = Str::snake(Str::pluralStudly($resource));
        $labelPluralStudly = Str::studly($pluralStudly);
        $namespace = "App\\{$studlyModule}";
        $resourceModel = "$namespace\\Entities\\$studlyResource";
        $directionModule = base_path('modules') .'/' . $module . '/src';

        $template = str_replace([
            '{{resourceName}}',
            '{{resourceLable}}',
            '{{namespace}}',
            '{{resourceModel}}',
            '{{moduleName}}',
            '{{resourcePermissions}}'
        ],[
            $studlyResource,
            $labelPluralStudly,
            $namespace,
            $resourceModel,
            $studlyModule,
            $pluralStudly
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Resources")) {
            $this->files->makeDirectory("{$directionModule}/Resources",0755, true);
        }

        file_put_contents("{$directionModule}/Resources/{$studlyResource}.php", $template);
        $this->info("Resource $studlyResource has been created!");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/resource.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Resource name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}