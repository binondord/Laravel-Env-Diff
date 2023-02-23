<?php

namespace romanzipp\EnvDiff\Types;

class ConfigWriterDataType
{
    public string $configFile;

    public string $key;

    public array $keyValuePair = [];

    public function setConfigFile($configFile) 
    {
        $this->configFile = $configFile;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function getData() 
    {
        return $this->keyValuePair;
    }

    public function setKeyValue($key, $value) 
    {
        $this->keyValuePair[$key] = $value;
    }
}