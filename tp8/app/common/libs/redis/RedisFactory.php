<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\redis;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * Redis 连接生成器
 */
class RedisFactory
{
    /**
     * @var array
     */
    private static $instance = [];

    /**
     * 创建连接
     * @param string $name
     * @return Client
     * @throws \Exception|ConnectionException
     */
    static function connection($name = 'default')
    {
        if (isset(self::$instance[$name])) {
            return self::$instance[$name];
        }
        $connections = config('redis.connections');
        if (!isset($connections[$name])) {
            throw new \Exception('redis connection not found:' . $name);
        }
        return self::$instance[$name] = new Client($connections[$name], []);
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}