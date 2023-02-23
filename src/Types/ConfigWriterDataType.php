<?php

namespace romanzipp\EnvDiff\Console\Types;

class ConfigWriterDataType
{
    public string $configFile;

    public array $keyValuePair = [];

    public function setConfigFile($configFile) 
    {
        $this->configFile = $configFile;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function getKeyValue() 
    {
        return $this->$keyValuePair;
    }
}