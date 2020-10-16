<?php
/**
 * User: jayinton
 */

namespace app\admin\service;


use app\common\service\BaseService;
use think\facade\Db;

class ConfigFieldService extends BaseService
{
    function getConfigFielList()
    {
        $lists = Db::name('config_field')->select()->toArray();
        foreach ($lists as &$item) {
            $item['setting'] = unserialize($item['setting']);
        }

        return self::createReturn(true, $lists);
    }

    /**
     * 添加后删除配置项
     *
     * @param $configField
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function addOrUpdateConfigField($configField)
    {
        if (isset($configField['fid']) && !empty($configField['fid'])) {
            // 更新
            $fid = $configField['fid'];
            unset($configField['fid']);
            $originConfig = Db::name('config_field')->where('fid', $fid)->findOrEmpty();
            if (empty($originConfig)) {
                return self::createReturn(false, null, '找不到信息');
            }
            Db::name('config_field')->where('fid', $fid)->update([
                'fieldname' => $configField['fieldname'],
                'type'      => $configField['type'],
                'setting'   => serialize($configField['setting']),
            ]);

            // 配置字段调整
            Db::name('config')->where('varname', $originConfig['fieldname'])->update([
                'varname' => $configField['fieldname'],
                'info'    => $configField['setting']['title'] ?: '',
            ]);

            return self::createReturn(true, null, '操作完成');
        }
        // 新增
        Db::startTrans();
        $res1 = Db::name('config_field')->insert([
            'fieldname'  => $configField['fieldname'],
            'type'       => $configField['type'],
            'setting'    => serialize($configField['setting']),
            'createtime' => time(),
        ]);


        // 配置字段调整
        $res2 = Db::name('config')->insert([
            'varname' => $configField['fieldname'],
            'info'    => $configField['setting']['title'] ?: '',
            'groupid' => 2,
            'value'   => ''
        ]);
        if (!$res1 || !$res2) {
            Db::rollback();
        }

        Db::commit();
        return self::createReturn(true, null, '操作完成');
    }

    function getConfigField($fid)
    {
        $configField = Db::name('config_field')->where('fid', $fid)->findOrEmpty();
        if (!empty($configField)) {
            $configField['setting'] = unserialize($configField['setting']);
            return self::createReturn(true, $configField);
        }

        return self::createReturn(false, null);
    }

}