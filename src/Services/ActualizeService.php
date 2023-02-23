<?php

namespace romanzipp\EnvDiff\Services;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Wujunze\Colors;

class ActualizeService extends DiffService
{
    public function getMissingFiles()
    {
        print_r($this->fileExistStatus);
    }
}