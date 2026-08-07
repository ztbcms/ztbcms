<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\common\service\kv;

use app\common\model\kv\KvModel;
use app\common\service\BaseService;
use think\facade\Cache;

/**
 * Key-value 组件
 * 注：需要配合 redis 缓存使用，请配置`cache.php`,开启`type`为`redis`
 */
class KV extends BaseService
{
    /**
     * 添加 key
     * 若存在则返回添加失败
     * @param $key
     * @param $value
     * @return bool
     */
    static function addKv($key, $value)
    {
        try {
            $model = new KvModel();
            $res = $model->save([
                'key' => $key,
                'value' => $value,
            ]);
            return !!$res;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 覆写 key-value
     * @param $key
     * @param $value
     * @return bool
     */
    static function setKv($key, $value)
    {
        $model = KvModel::where('key', $key)->find();
        if (!$model) {
            return self::addKv($key, $value);
        }
        $model->value = $value;
        return !!$model->save();
    }

    /**
     * 覆写 key-value 并设置缓存
     * @param $key
     * @param $value
     * @param int $ttl 缓存时间
     * @return bool
     */
    static function setKvWithCache($key, $value, $ttl = 60)
    {
        $res = self::setKv($key, $value);
        if ($res) {
            Cache::set($key, $value, $ttl);
        }
        return $res;
    }

    /**
     * 获取 kv
     * @param $key string
     * @param $defaultValue string|null 默认值
     * @return mixed|null
     */
    static function getKv($key, $defaultValue = null)
    {
        $model = KvModel::where('key', $key)->find();
        if (!$model) {
            return $defaultValue;
        }
        return $model->value;

    }

    /**
     * 获取 kv 缓存
     * @param $key
     * @param null $defaultValue string|null 默认值
     * @param int $ttl 缓存时间
     * @return mixed|null
     */
    static function getKvWithCache($key, $defaultValue = null, $ttl = 60)
    {
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        $value = self::getKv($key, $defaultValue);
        Cache::set($key, $value, $ttl);
        return $value;

    }

    /**
     * 删除 kv
     * @param $key
     * @return bool
     */
    static function delKv($key)
    {
        $res = KvModel::where('key', $key)->delete();
        Cache::delete($key);
        return !!$res;
    }

    /**
     * 删除超时的 KV
     * @param $key
     * @param $ttl
     * @return bool
     */
    static function delExpiredKv($key, $ttl)
    {
        $model = KvModel::where('key', $key)->find();
        if (!$model) {
            return false;
        }
        $update_time = strtotime($model->update_time);
        if (time() - $update_time > $ttl) {
            return self::delKv($key);
        }
        return false;
    }
}