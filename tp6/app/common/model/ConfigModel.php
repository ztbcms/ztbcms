<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-07
 * Time: 09:36.
 */

namespace app\common\model;


use app\admin\service\AdminConfigService;
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
     * 获取配置
     *
     * @param  bool  $force
     *
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getConfigs($force = false)
    {
        return AdminConfigService::getInstance()->getConfig(null, !$force)['data'];
    }
}