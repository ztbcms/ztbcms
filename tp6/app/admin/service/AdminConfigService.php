<?php
/**
 * User: jayinton
 * Date: 2020/9/19
 */

namespace app\admin\service;


use app\common\service\BaseService;
use think\facade\Db;

/**
 * 管理后台配置
 * Class AdminConfigService
 *
 * @package app\admin\service
 */
class AdminConfigService extends BaseService
{
    static function getInstance()
    {
        return new AdminConfigService();
    }

    /**
     * 获取配置
     *
     * @param  string  $key 配置的key,为空的时候返回全部
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getConfig($key = '')
    {
        //TODO 缓存config
        $configList = Db::name('config')->field('varname,value')->select()->toArray();
        $config = [];
        foreach ($configList as $i => $v) {
            $config[$v['varname']] = $v['value'];
        }
        if (!empty($key)) {
            return self::createReturn(true, $config[$key]);
        }
        return self::createReturn(true, $config);
    }

    /**
     * 更新配置项
     *
     * @param  array $keyValue
     *
     * @return array
     * @throws \think\db\concern\PDOException
     * @throws \think\db\exception\DbException
     */
    function updateConfig(array $keyValue = []){
        Db::startTrans();
        foreach ($keyValue as $key => $value){
            Db::name('config')->where('varname', $key)->update([
                'value' => $value
            ]);
        }
        Db::commit();

        return self::createReturn(true, null, '配置更新完成');
    }

    function getConfigFielList(){
        $lists = Db::name('config_field')->select()->toArray();
        foreach ($lists as &$item){
            $item['setting'] = unserialize($item['setting']);
        }

        return self::createReturn(true, $lists);
    }
}