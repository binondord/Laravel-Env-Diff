<?php

namespace romanzipp\EnvDiff\Console\Commands;

use Illuminate\Console\Command;
use Dotenv\Exception\InvalidPathException;
use romanzipp\EnvDiff\Services\DiffService;
use romanzipp\EnvDiff\Services\ScanForEnvs;
use romanzipp\EnvDiff\Services\ConfigWriter;
use romanzipp\EnvDiff\Types\ConfigWriterDataType;

class DiffEnvFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:diff
                            {files? : Specify environment files, overriding config}
                            {conf? : Specify config files, overriding default env-diff.php}
                            {--actualize : Actualize differences from two env files}
                            {--values : Display existing environment values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a visual Diff of .env and .env.example files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $configFile = "env-diff";
        if ($overrideConfigFileName = $this->argument('conf')) 
        {
            $configFile = $overrideConfigFileName;
        }

        $localConfigFileExist = is_file(config_path($configFile.'.php'));

        if (! $localConfigFileExist)
        {
            $this->info('Config file does not exist. Creating one.');
            $this->call('vendor:publish', [
                '--provider' => 'romanzipp\EnvDiff\Providers\EnvDiffProvider'
            ]);
        }

        $service = new DiffService();

        $files = config($configFile . '.files') ?: ['.env'];

        $scanner = new ScanForEnvs();
        $scannedFiles = $scanner->scan($service->getConfig(), $service->getPath());
        
        $diff_files = array_diff($scannedFiles, $files);
        $hasScanDiff = count($diff_files) > 0;
        if ($hasScanDiff) {
            $scanner->displayTable();
        }

        if ($hasScanDiff > 0 && 
            $this->confirm('Found more .env files compare to what\'s currently set in config file. Do you want to update the config file?', true))  {
            $configWriter = new ConfigWriter();
            $configWriter->write(
                $configWriter->setup([
                'configFile' => $configFile,
                'key' => 'files',
                'value' => $scannedFiles,
            ]));
        }

        if ($overrideFiles = $this->argument('files')) {
            $files = explode(',', $overrideFiles);
        }

        if (true === $this->option('values')) {
            $service->config['show_values'] = true;
        }

        $service->add($files);

        $service->displayTable();

        if (true === $this->option('actualize')) {
            $targetFile = ".env";
            $baseFile = ".env.example";
            if (count($files) != 2) {
                $targetFile = $this->choice(
                    'Select your target file?',
                    $scannedFiles,
                    0
                );

                $this->info("You've selected: $targetFile");

                $baseChoices = array_filter($scannedFiles, function($file) use($targetFile) {
                    return $file !== $targetFile;
                });

                $baseFile = $this->choice(
                    'Select your base file?',
                    $baseChoices,
                    key($baseChoices)
                );

                $this->info("You've selected: $baseFile");

                $this->info("Comparing $targetFile vs $baseFile");

                $this->call("env:diff", [
                    "files" => "$targetFile,$baseFile",
                    "--actualize" => true,
                    "--values" => true
                ]);
            }

            if (count($files) == 2 &&
                    count($service->diff()) &&
                    $this->confirm("Actualizing difference from $targetFile,$baseFile. Do you want to continue?", true)
                ) {
                $this->info("running...");

                $appendText = "";

                foreach($service->diff() as $diffKey => $diffFileCompared) 
                {
                    $existing = $service->getData($baseFile)[$diffKey] ?? null;
                    $targetExistingValue = $service->getData($targetFile)[$diffKey] ?? null;

                    if (null !== $existing) {
                        $exampleValue = $existing;
                        $textCurrentValue = "$diffKey=";

                        if (null !== $targetExistingValue) {
                            $textCurrentValue = (null !== $targetExistingValue) ? "$diffKey=$targetExistingValue" : "$diffKey=";
                        }

                        if (!empty($exampleValue)) {
                            $finalValue = $this->ask($textCurrentValue, $exampleValue);
                        } else {
                            $finalValue = $this->ask($textCurrentValue);
                        }

                        $this->info("You've entered: $finalValue");

                        $appendText .= "$diffKey=$finalValue\n";
                    }
                }

                $envContent = file_get_contents(base_path("$targetFile"));
                file_put_contents(base_path("$targetFile-backup-".date("Y-m-d-His")), $envContent);
                $envContent .= $appendText;
                file_put_contents(base_path("$targetFile"), $envContent);

                $this->info("done!");
            }
        }
    }
}
