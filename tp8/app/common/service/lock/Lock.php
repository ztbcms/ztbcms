<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\common\service\lock;

use app\common\service\BaseService;
use app\common\service\kv\KV;

/**
 * 基于 kv 的锁实现
 */
class Lock extends BaseService
{
    /**
     * 申请锁
     * @param $key string 锁的 key
     * @param $val string 锁的值
     * @param $ttl int 锁的超时时间
     * @return bool 是否申请成功
     */
    static function acquire($key, $val, $ttl = 5)
    {
        throw_if($ttl <= 0, new \Exception('ttl must be greater than 0'));
        throw_if(empty($key), new \Exception('key must not be empty'));
        $res = KV::addKv($key, $val);
        if (!$res) {
            // 如果添加失败，说明已经有锁
            // 尝试处理超时
            if (KV::delExpiredKv($key, $ttl)) {
                // 再次尝试申请锁
                return KV::addKv($key, $val);
            }
            return false;
        }
        return true;
    }

    /**
     * 释放锁
     * @param $key
     * @return bool 是否释放成功
     */
    static function release($key)
    {
        return KV::delKv($key);
    }
}