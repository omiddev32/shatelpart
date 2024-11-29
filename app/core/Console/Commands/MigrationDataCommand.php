<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use App\Core\MigrationDataRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrationDataCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data';

    /**
     *  All data.
     */
    protected $data;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $check = new MigrationDataRepository();
        $steps = [
            'Scanning modules...' => 'scanModules',
            'Checking permissions...' => 'checkPermissions',
            'Checking roles...' => 'checkRoles',
            'Checking data...' => 'checkData',
        ];
        $progress = new ProgressBar($this->output, count($steps));
        $progress->start();

        foreach ($steps as $message => $function) {
            $progress->setMessage($message);
            $this->$function($check);
            $progress->advance();
        }
        $progress->finish();
        $this->info("\n\nEnd process.");
    }

    /**
     * Scanning modules
     *
     */
    private function scanModules($function)
    {
        $this->data = $function->getModules();
    }

    /**
     * Checking permissions
     *
     */
    private function checkPermissions($function)
    {
        $function->checkPermissions($this->data);
    }

    /**
     * Checking roles
     *
     */
    private function checkRoles($function)
    {
        $function->checkRoles($this->data);
    }
    /**
     * Checking data
     *
     */
    private function checkData($function)
    {
        $function->checkData($this->data);
    }
}