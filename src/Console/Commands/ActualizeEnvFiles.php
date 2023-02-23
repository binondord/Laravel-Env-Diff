<?php

namespace romanzipp\EnvDiff\Console\Commands;

use Illuminate\Console\Command;
use Dotenv\Exception\InvalidPathException;
use romanzipp\EnvDiff\Services\ActualizeService;

class ActualizeEnvFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:actualize
                            {files? : Specify environment files, overriding config}
                            {--values : Display existing environment values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualize a visual Diff of .env and .env.example files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = config('env-diff.files') ?: ['.env'];

        if ($overrideFiles = $this->argument('files')) {
            $files = explode(',', $overrideFiles);
        }

        $service = new ActualizeService();

        if (true === $this->option('values')) {
            $service->config['show_values'] = true;
        }
        
        $service->add($files);

        #$service->displayTable();
        $service->getMissingFiles();
    }
}
