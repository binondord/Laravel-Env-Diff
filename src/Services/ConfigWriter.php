<?php

namespace romanzipp\EnvDiff\Services;

use romanzipp\EnvDiff\Types\ConfigWriterDataType;

class ConfigWriter
{
    public function write(ConfigWriterDataType $configWriterData)
    {
        $configFile = $configWriterData->getConfigFile();
        $data = $configWriterData->getData();
        $configFileContent = file_get_contents(config_path($configFile.'.php'));

        foreach($data as $key => $value) 
        {
            $pattern = $this->usePatternByType($value);
            $replacement = '${1} => '.$this->processType($value).',';
            $text = preg_replace($pattern, $replacement, $configFileContent);
            file_put_contents(config_path($configFile.'.php'), $text);
        }
    }

    private function usePatternByType($value)
    {
        switch (gettype($value)) {
            case 'array':
                return [
                    "/('files').*=> (\[.*\],)/s",
                    "/('files').*=>\s+(array \(.*\),)/s",
                    "/('files').*=> (\[\],)/s",
                ];
                break;
            default:
                return "/('$key'.*)=>.*,/";
                break;
        }
    }

    private function processType($value)
    {
        switch (gettype($value)) {
            case 'array':
                $result = var_export($value, true);
                $result = preg_replace("/^/m", "\t\t", $result);
                return $result;
                break;
            case 'string':
                return "'".$value."'";
                break;
            default:
                return $value;
                break;
        }
    }

    public function setup($data): ConfigWriterDataType
    {
        $configWriterDataType = new ConfigWriterDataType();
        $configWriterDataType->setConfigFile($data['configFile']);
        $configWriterDataType->setKeyValue($data['key'], $data['value']);
        return $configWriterDataType;
    }
}