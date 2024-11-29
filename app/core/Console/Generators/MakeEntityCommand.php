<?php

namespace App\Core\Console\Generators;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use App\Core\Console\GeneratorCommand;

class MakeEntityCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module:entity
    	{slug : The slug of the module.}
    	{name : The name of the entity class.}
        {--m : Create a new migration file for the entity.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module entity class';

    /**
     * String to store the command type.
     *
     * @var string
     */
    protected $type = 'Module entity';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() !== false) {
            if ($this->option('m')) {
                $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

                $this->call('make:module:migration', [
                    'slug' => $this->argument('slug'),
                    'name' => "create_{$table}_table",
                    '--create' => $table,
                ]);
            }
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('core/Stubs/entity.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return module_class($this->argument('slug'), 'Entities');
    }
    
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['m', 'migration', InputOption::VALUE_NONE, 'Create a new migration file for the entity'],
        ];
    }
}
