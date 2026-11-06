<?php

use think\facade\Db;
use think\migration\Migrator;

/**
 * 注册管理后台队列监控菜单和权限节点
 */
class InsertQueueMonitorMenu extends Migrator
{
    /**
     * 注册菜单
     *
     * @return void
     */
    public function up()
    {
        $rootId = intval(Db::name('menu')
            ->where('app', 'common')
            ->where('controller', 'index')
            ->where('action', 'index')
            ->value('id'));
        if ($rootId <= 0) {
            throw new RuntimeException('系统管理父级菜单不存在');
        }

        $groupId = intval(Db::name('menu')
            ->where('parentid', $rootId)
            ->where('app', 'common')
            ->where('controller', 'queue.dashboard')
            ->where('action', 'index')
            ->value('id'));
        if ($groupId <= 0) {
            $groupId = intval(Db::name('menu')->insertGetId([
                'name'      => '队列监控',
                'parentid'  => $rootId,
                'app'       => 'common',
                'controller'=> 'queue.dashboard',
                'action'    => 'index',
                'parameter' => '',
                'type'      => 0,
                'status'    => 1,
                'remark'    => '',
                'listorder' => 0,
                'icon'      => '',
            ]));
        }

        $children = [
            ['index', '概况', 1],
            ['monitorLog', '执行记录', 1],
            ['monitorDetail', '执行记录详情', 0],
            ['failedJobs', '失败任务', 1],
            ['failedJobDetail', '失败任务详情', 0],
            ['retryFailedJob', '重试失败任务', 0],
            ['deleteFailedJob', '删除失败任务', 0],
        ];
        foreach ($children as $item) {
            $exists = Db::name('menu')
                ->where('parentid', $groupId)
                ->where('app', 'common')
                ->where('controller', 'queue.dashboard')
                ->where('action', $item[0])
                ->count();
            if ($exists > 0) {
                continue;
            }
            Db::name('menu')->insert([
                'name'      => $item[1],
                'parentid'  => $groupId,
                'app'       => 'common',
                'controller'=> 'queue.dashboard',
                'action'    => $item[0],
                'parameter' => '',
                'type'      => 1,
                'status'    => $item[2],
                'remark'    => '',
                'listorder' => 0,
                'icon'      => '',
            ]);
        }
    }

    /**
     * 删除队列监控菜单
     *
     * @return void
     */
    public function down()
    {
        Db::name('access')
            ->where('app', 'common')
            ->where('controller', 'queue.dashboard')
            ->delete();
        Db::name('menu')
            ->where('app', 'common')
            ->where('controller', 'queue.dashboard')
            ->delete();
    }
}
