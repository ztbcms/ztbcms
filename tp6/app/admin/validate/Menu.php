<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 18:18
 */

namespace app\admin\validate;

use think\Validate;
use app\admin\model\MenuModel;

class Menu extends Validate
{

    protected $rule = [
        'name'       => 'require',
        'app'        => 'require',
        'controller' => 'require',
        'action'     => 'require',
        'icon'       => 'checkIcon',
    ];

    protected $message = [
        'name.require'       => '名称不能为空！',
        'app.require'        => '模块不能为空',
        'controller.require' => '控制器不能为空！',
        'action.require'     => '方法不能为空',
    ];

    /**
     * 校验菜单图标类名格式
     *
     * @param mixed $value 图标值
     * @return bool|string
     */
    protected function checkIcon($value)
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!MenuModel::isValidIcon($value)) {
            return '图标格式不正确，仅支持字母、数字、下划线和中划线，且长度不超过 ' . MenuModel::ICON_MAX_LENGTH . ' 个字符';
        }
        return true;
    }

}
