<?php

namespace App\Core\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakePartitionCommand extends Command
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
    protected $signature = 'make:module:partition     
        {slug : The slug of the module.}
        {name : The name of the partition class.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module partition metric.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('slug');

        $partition = $this->argument('name');
        $studlyModule = Str::studly($module);
        $studlyPartition = Str::studly($partition);
        $namespace = "App\\{$studlyModule}";
        $uriKey = Str::kebab($partition);

        $directionModule = base_path('modules') .'/' . $module . '/src';
        $template = str_replace([
            '{{partitionName}}',
            '{{namespace}}',
            '{{uriKey}}',
        ],[
            $studlyPartition,
            $namespace,
            $uriKey
        ], file_get_contents($this->getStub()));

        if (!$this->files->isDirectory("{$directionModule}/Metrics")) {
            $this->files->makeDirectory("{$directionModule}/Metrics",0755, true);
        }

        file_put_contents("{$directionModule}/Metrics/{$studlyPartition}.php", $template);

        $this->info("Partition $studlyPartition has been created!");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/partition.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Partition name'],
            ['slug', InputArgument::REQUIRED, 'Module name']
        ];
    }
}