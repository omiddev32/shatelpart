<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Filesystem\Filesystem;

class MakeCardCommand extends Command
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
    protected $signature = 'make:module:card     
        {slug : The slug of the module.}
        {name : The name of the card class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module card class.';

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

        if (!$this->files->isDirectory("{$directionModule}/Cards")) {

            $this->files->makeDirectory("{$directionModule}/Cards",0755, true);
        }

        file_put_contents("{$directionModule}/Cards/{$studlyName}.php" ,$template);

        $this->info("Card $studlyName has been created!");

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/card.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Card name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}