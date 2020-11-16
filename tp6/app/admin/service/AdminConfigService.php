<?php
/**
 * User: jayinton
 * Date: 2020/9/19
 */

namespace app\admin\service;


use app\common\service\BaseService;
use think\facade\Cache;
use think\facade\Db;

/**
 * 管理后台配置
 * Class AdminConfigService
 *
 * @package app\admin\service
 */
class AdminConfigService extends BaseService
{
    /**
     * 缓存Tag名称
     */
    const CacheTagName = 'AdminConfig';

    static function getInstance()
    {
        return new AdminConfigService();
    }

    /**
     * 生成缓存key
     *
     * @param  string  $key
     *
     * @return string
     */
    private function _makeAdminConfigCacheKey($key = '')
    {
        return 'AdminConfig_'.$key;
    }

    /**
     * 获取配置
     *
     * @param  string  $key  配置的key,为空的时候返回全部
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getConfig($key = '')
    {
        $cache_key = $this->_makeAdminConfigCacheKey($key);
        $cache_data = Cache::get($cache_key);
        if (!empty($cache_data)) {
            return self::createReturn(true, $cache_data);
        }

        $configList = Db::name('config')->field('varname,value')->select()->toArray();
        $config = [];
        foreach ($configList as $i => $v) {
            $config[$v['varname']] = $v['value'];
        }
        if (!empty($key)) {
            $value = isset($config[$key]) ? $config[$key] : null;
            Cache::tag(self::CacheTagName)->set($cache_key, $value);
            return self::createReturn(true, $value);
        }
        Cache::tag(self::CacheTagName)->set($cache_key, $config);
        return self::createReturn(true, $config);
    }

    /**
     * 更新配置项
     *
     * @param  array  $keyValue
     *
     * @return array
     * @throws \think\db\concern\PDOException
     * @throws \think\db\exception\DbException
     */
    function updateConfig(array $keyValue = [])
    {
        foreach ($keyValue as $key => $value) {
            $res = Db::name('config')->where('varname', $key)->update([
                'value' => $value
            ]);
            if ($res) {
                $cache_key = $this->_makeAdminConfigCacheKey($key);
                Cache::tag(self::CacheTagName)->set($cache_key, $value);
            }

        }
        return self::createReturn(true, null, '配置更新完成');
    }

    /**
     * 获取配置字段列表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getConfigFielList()
    {
        $lists = Db::name('config_field')->select()->toArray();
        foreach ($lists as &$item) {
            $item['setting'] = unserialize($item['setting']);
        }

        return self::createReturn(true, $lists);
    }

    /**
     * 清理全部配置缓存
     */
    function clearConfigCache()
    {
        Cache::tag(self::CacheTagName)->clear();
    }
}