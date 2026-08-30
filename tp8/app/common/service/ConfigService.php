<?php
/**
 * User: zhlhuang
 */

namespace app\common\service;

use app\common\model\ConfigModel;

class ConfigService
{
    static $_instance;

    private $config_list;

    private function __construct()
    {
        $this->config_list = (new ConfigModel)->where([])
            ->column('value', 'varname');
    }

    public static function getInstance(): ConfigService
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getConfig($key, $default = '')
    {
        return $this->config_list[$key] ?? $default;
    }

    public function updateConfig(array $keyValue = []): bool
    {
        foreach ($keyValue as $key => $value) {
            $config = (new ConfigModel())->where('varname', $key)
                ->findOrEmpty();
            $config->save([
                'varname' => $key,
                'value' => $value
            ]);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getConfigList(): array
    {
        return $this->config_list;
    }
}