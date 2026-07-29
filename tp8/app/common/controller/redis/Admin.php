<?php
/**
 * Author: Jayin Tuang <tonjayin@gmail.com>
 */

namespace app\common\controller\redis;

use app\common\controller\AdminController;
use app\common\libs\redis\RedisFactory;
use Predis\Connection\ConnectionException;
use think\Request;

/**
 * Redis 管理
 */
class Admin extends AdminController
{

    /**
     * 概览
     * @param Request $request
     * @return \think\response\Json|\think\response\View
     * @throws \Exception
     */
    public function dashboard(Request $request)
    {
        if ($request->isGet() && $request->get('_action') == 'getDashboard') {
            $connections = config('redis.connections');
            $ret = [];
            foreach ($connections as $key => $config) {

                try {
                    $redis = RedisFactory::connection($key);
                    $info = $redis->info();
                    $ret [] = [
                        'connection' => $key,
                        'running' => true,
                        'info' => [
                            // Redis 服务器版本
                            'redis_version' => $info['Server']['redis_version'],
                            // 已启动天数
                            'uptime_in_days' => $info['Server']['uptime_in_days'],
                            // 分配的内存总量
                            'used_memory_human' => $info['Memory']['used_memory_human'],
                            // 内存消耗峰值
                            'used_memory_peak_human' => $info['Memory']['used_memory_peak_human'],
                            // 连接的客户端数
                            'connected_clients' => $info['Clients']['connected_clients'],
                            // 自启动起连接过的总数
                            'total_connections_received' => $info['Stats']['total_connections_received'],
                        ],
                    ];
                } catch (ConnectionException $e) {
                    $ret [] = [
                        'connection' => $key,
                        'runing' => false,
                        'version' => '-',
                        'info' => [],
                    ];
                }


            }
            return self::makeJsonReturn(true, $ret);
        }
        return view();
    }
}