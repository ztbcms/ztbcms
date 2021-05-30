<?php

namespace clagiordano\weblibs\configmanager;

use Exception;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfigManager
 * @package clagiordano\weblibs\configmanager
 */
class YamlConfigManager extends AbstractConfigManager
{
    /**
     * Load config data from file and store it into internal property
     *
     * @param null|string $configFilePath
     *
     * @return IConfigurable
     */
    public function loadConfig($configFilePath = null)
    {
        if (!is_null($configFilePath)) {
            $this->configFilePath = $configFilePath;

            if (file_exists($configFilePath)) {
                try {
                    $this->configData = Yaml::parse(file_get_contents($configFilePath));
                } catch (Exception $exception) {
                    throw new RuntimeException(
                        "Failed to read config file from path '{$configFilePath}'\n{$exception->getMessage()}"
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Prepare and write config file on disk
     *
     * @param null|string $configFilePath
     * @param bool $autoReloadConfig
     *
     * @return IConfigurable
     * @throws RuntimeException
     */
    public function saveConfigFile($configFilePath = null, $autoReloadConfig = false)
    {
        if (is_null($configFilePath)) {
            $configFilePath = $this->configFilePath;
        }

        try {
            file_put_contents($configFilePath, Yaml::dump($this->configData, 2, 2));
        } catch (Exception $exception) {
            throw new RuntimeException(
                "Failed to write config file to path '{$configFilePath}'\n{$exception->getMessage()}"
            );
        }

        if ($autoReloadConfig) {
            $this->loadConfig($configFilePath);
        }

        return $this;
    }
}
