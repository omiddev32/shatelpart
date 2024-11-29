<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeDataCommand extends Command
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
    protected $signature = 'make:module:data     
        {slug : The slug of the module.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a data for a certian module.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('slug');
        $directionModule = base_path('modules') .'/' . $module;

        if (! $this->files->isFile("{$directionModule}/data.yaml")) {
            file_put_contents("{$directionModule}/data.yaml" ,file_get_contents($this->getStub()));
            $this->info("Data has been created!");
        } else {
            $this->error("The {$module} module has a data.");
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/data.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}