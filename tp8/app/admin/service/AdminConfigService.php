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
 * 管理后台配置（建议使用 app\common\service\ConfigService）
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
     * @param  bool  $enable_cache  是否启用缓存
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getConfig($key = '', $enable_cache = true)
    {
        $key = $key ?? '';
        $cache_key = $this->_makeAdminConfigCacheKey($key);
        if ($enable_cache) {
            $cache_data = Cache::get($cache_key);
            if (!empty($cache_data)) {
                return self::createReturn(true, $cache_data);
            }
        }
        $value = '';
        if (empty($key)) {
            // key为空，则获取全部配置
            $configList = Db::name('config')->field('varname,value')->select()->toArray();
            $value = [];
            foreach ($configList as $i => $v) {
                $value[$v['varname']] = $v['value'];
            }
        } else {
            // key为指定配置项
            $config = Db::name('config')->where('varname', $key)->field('varname,value')->find();
            if ($config) {
                $value = $config['value'];
            }
        }
        if ($enable_cache) {
            Cache::tag(self::CacheTagName)->set($cache_key, $value);
        }
        return self::createReturn(true, $value);
    }

    /**
     * 更新配置项
     *
     * @param  array  $keyValue
     *
     * @return array
     */
    function updateConfig(array $keyValue = [])
    {
        foreach ($keyValue as $key => $value) {
            Db::name('config')->where('varname', $key)->update([
                'value' => $value
            ]);
        }
        return self::createReturn(true, null, '配置更新完成，更新缓存后生效');
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