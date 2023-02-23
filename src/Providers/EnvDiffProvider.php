<?php

namespace romanzipp\EnvDiff\Providers;

use Illuminate\Support\ServiceProvider;
use romanzipp\EnvDiff\Console\Commands\ActualizeEnvFiles;
use romanzipp\EnvDiff\Console\Commands\DiffEnvFiles;

use romanzipp\EnvDiff\Services\DiffService;
use romanzipp\EnvDiff\Services\View\ConsoleTable;

class EnvDiffProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../config/env-diff.php' => config_path('env-diff.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ActualizeEnvFiles::class,
                DiffEnvFiles::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/env-diff.php',
            'env-diff'
        );
    }
}
