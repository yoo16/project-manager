<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;

class MakeBlade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:blade {name?} {--dir=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new blade file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $dir = $this->option('dir');
        $blade_dir = resource_path().'/views/';
        $stub_base_dir = app_path() . '/Console/Commands/stubs/';

        if ($dir) {
            $blade_dir.= "{$dir}/";
            if (!file_exists($blade_dir)) {
                File::makeDirectory($blade_dir);
            }
        }

        if ($name) {
            $actions = [$name];
        } else {
            $actions = ['index', 'create', 'edit'];
        }

        foreach ($actions as $action) {
            $stub_file_name = "{$action}.blade.stub";
            $stub_file_path = "{$stub_base_dir}{$stub_file_name}";
            if (!file_exists($stub_file_path)) {
                $stub_file_path = "{$stub_base_dir}_default.blade.stub";
            }
            $blade_file = "{$action}.blade.php";
            $blade_path = "{$blade_dir}{$blade_file}";
            if (!file_exists($blade_path)) {
                $stub = File::get($stub_file_path);
                File::put($blade_path, $stub);
            }
        }
    }
}
