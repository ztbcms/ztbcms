<?php
/**
 * User: jayinton
 */

namespace app\admin\service;


use app\common\service\BaseService;
use think\facade\Db;

class ConfigFieldService extends BaseService
{
    /**
     * 获取拓展字段列表
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
     * 添加后删除配置项
     *
     * @param $configField
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function addOrUpdateConfigField($configField)
    {
        $setting = explode("\n", $configField['setting']['option']);
        $settingList = [];
        foreach ($setting as $item){
            $split = explode('|', $item);
            if(count($split) === 2){
                $settingList[] = [
                    'title' => $split[0],
                    'value' => $split[1],
                ];
            }
        }
        $configField['setting']['option'] = $settingList;

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

            //更新缓存的Config清理
            AdminConfigService::getInstance()->clearConfigCache();
            return self::createReturn(true, null, '操作完成');
        }
        // 新增

        // 检测是否存在
        $res = Db::name('config_field')->where('fieldname', $configField['fieldname'])->findOrEmpty();
        if($res){
            return self::createReturn(false, null, '键名已被占用');
        }
        $res = Db::name('config')->where('varname', $configField['fieldname'])->findOrEmpty();
        if($res){
            return self::createReturn(false, null, '键名已被占用');
        }

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
        //更新缓存的Config清理
        AdminConfigService::getInstance()->clearConfigCache();
        return self::createReturn(true, null, '操作完成');
    }

    /**
     * 获取配置字段
     * @param $fid
     *
     * @return array
     */
    function getConfigField($fid)
    {
        $configField = Db::name('config_field')->where('fid', $fid)->findOrEmpty();
        if (!empty($configField)) {
            $configField['setting'] = unserialize($configField['setting']);
            return self::createReturn(true, $configField);
        }

        return self::createReturn(false, null);
    }

    /**
     * 删除拓展配置字段
     * @param $fid
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function deleteConfigField($fid){
        if (empty($fid)) {
            return self::createReturn(false, null, '请指定需要删除的扩展配置项');
        }

        //扩展字段详情
        $config = $this->getConfigField($fid)['data'];
        if (empty($config)) {
            return self::createReturn(false, null, '该扩展配置项不存在');
        }
        //删除
        Db::startTrans();
        $res1 = Db::name('config_field')->where('fid', $fid)->delete();
        $res2 = Db::name('config')->where('varname', $config['fieldname'])->delete();

        if($res1 && $res2){
            Db::commit();
            AdminConfigService::getInstance()->clearConfigCache();
            return self::createReturn(true, null, '操作完成');
        }

        Db::rollback();
        return self::createReturn(true, null, '操作完成');
    }

}