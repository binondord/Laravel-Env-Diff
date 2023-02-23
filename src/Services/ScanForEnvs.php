<?php

namespace romanzipp\EnvDiff\Services;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Wujunze\Colors;
use romanzipp\EnvDiff\Services\View\ConsoleTable;

class ScanDirForEnvs
{
    /**
     * @var array<string, mixed>
     */
    public $fileExistStatus = [];

    /**
     * @var boolean
     */
    protected $hasMissingFiles = false;

    public function scanDirForEnvs($config, $path): array
    {
        $this->config = $config;
        $envFile = $this->config['file_target'];
        $suffix = $this->config['file_dist_suffix'];

        $files = glob($path.'/'.$envFile.'*');

        $fileNames = [];
        foreach($files as $file) {
            $fileNames[] = basename($file);
        }
        return $fileNames;
    }

    public function fileExists($path, $envFile): bool
    {
        return is_file($path . '/' . $envFile);
    }

    public function markFileStatus($envFile): bool
    {
        $exists = $this->fileExists($envFile);
        $this->fileExistStatus[] = [
            $envFile => $exists,
        ];

        return $exists;
    }

    public function missingFilesExists()
    {
        return $this->hasMissingFiles;
    }
}