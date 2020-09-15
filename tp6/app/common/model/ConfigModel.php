<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-07
 * Time: 09:36.
 */

namespace app\common\model;


use think\facade\Cache;
use think\Model;

/**
 *  网站配置
 * Class ConfigModel
 * @package app\common\model
 */
class ConfigModel extends Model
{
    protected $name = 'config';
    protected $pk = "varname";


    static function editConfigs($configs)
    {
        $updataData = [];
        foreach ($configs as $key => $value) {
            $updataData[] = ['varname' => $key, 'value' => $value];
        }
        $configModel = new self();
        $configModel->saveAll($updataData);
        return self::getConfigs(true);
    }

    /**
     * 获取数据
     * @param bool $force
     * @return array|mixed
     */
    static function getConfigs($force = false)
    {
        if (Cache::has('Configs') && !$force) {
            return Cache::get('Configs');
        } else {
            $configs = self::column('value', 'varname');
            Cache::set('Configs', $configs);
            return $configs;
        }
    }
}