<?php

namespace romanzipp\EnvDiff\Services;

use romanzipp\EnvDiff\Type\ConfigWriterDataType;

class ConfigWriter
{
    public function write(ConfigWriterDataType $configWriterData)
    {
        $configFile = $configWriterData->getConfigFile();
        $configFile = $configWriterData->getKeyValue();
        config([$configFile.'.YOUR_KEY' => 'NEW_VALUE']);
        $text = '<?php return ' . var_export(config($configFile), true) . ';';
        file_put_contents(config_path($configFile.'.php'), $text);
    }
}