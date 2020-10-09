<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\service;


use app\common\service\BaseService;

/**
 * 模块管理
 * Class ModuleService
 *
 * @package app\admin\service
 */
class ModuleService extends BaseService
{
    /**
     * 是否已安装
     * @param $moduleName
     *
     * @return bool
     */
    function isInstall($moduleName){
        return true;
    }

    /**
     * 安装模块
     * @param $moduleName
     *
     * @return bool
     */
    function install($moduleName){
        return true;
    }

    /**
     * 卸载模块
     * @param $moduleMame
     */
    function uninstall($moduleMame){
        return true;
    }


}