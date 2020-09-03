<?php

// +----------------------------------------------------------------------
// |  站点配置
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;

class ConfigController extends AdminBase
{

    private $Config = null;

    protected function _initialize()
    {
        parent::_initialize();
        $this->Config = D('Common/Config');
        $configList = $this->Config->getField("varname,value");
        $this->assign('Site', $configList);
    }

    //网站基本设置
    public function index()
    {
        if (IS_POST) {
            if ($this->Config->saveConfig($_POST)) {
                $this->success("更新成功！");
            } else {
                $error = $this->Config->getError();
                $this->error($error ? $error : "配置更新失败！");
            }
        } else {
            //首页模板
            $filepath = TEMPLATE_PATH . (empty(self::$Cache["Config"]['theme']) ? 'Default' : self::$Cache["Config"]['theme']) . '/Content/Index/';
            $indextp = str_replace($filepath, '', glob($filepath . 'index*'));
            //URL规则
            $Urlrules = cache('Urlrules');
            $IndexURL = array();
            $TagURL = array();
            foreach ($Urlrules as $k => $v) {
                if ($v['file'] == 'tags') {
                    $TagURL[$v['urlruleid']] = $v['example'];
                }
                if ($v['module'] == 'content' && $v['file'] == 'index') {
                    $IndexURL[$v['ishtml']][$v['urlruleid']] = $v['example'];
                }
            }
            $this->assign('TagURL', $TagURL)
                ->assign('IndexURL', $IndexURL)
                ->assign('indextp', $indextp)
                ->display();
        }
    }

    //邮箱参数
    public function mail()
    {
        if (IS_POST) {
            $this->index();
        } else {
            $this->display();
        }
    }

    //附件参数
    public function attach()
    {
        if (IS_POST) {
            $this->index();
        } else {
            $path = PROJECT_PATH . 'Libs/Driver/Attachment/';
            $dirverList = glob($path . '*');
            $lang = array(
                'Local' => '本地存储驱动',
                'Ftp' => 'FTP远程附件驱动',
                'Aliyun' => '阿里云OSS上传驱动【暂不支持水印】',
            );
            foreach ($dirverList as $k => $rs) {
                unset($dirverList[$k]);
                $dirverName = str_replace(array($path, '.class.php'), '', $rs);
                $dirverList[$dirverName] = $lang[$dirverName] ?: $dirverName;
            }
            $this->assign('dirverList', $dirverList);
            $this->display();
        }
    }

    //高级配置
    public function addition()
    {
        if (IS_POST) {
            if ($this->Config->addition($_POST)) {
                $this->success("修改成功，请及时更新缓存！");
            } else {
                $error = $this->Config->getError();
                $this->error($error ? $error : "高级配置更新失败！");
            }
        } else {
            $addition = include COMMON_PATH . 'Conf/addition.php';
            if (empty($addition) || !is_array($addition)) {
                $addition = array();
            }
            $this->assign("addition", $addition);
            $this->display();
        }
    }

    //扩展配置
    public function extend()
    {
        if (IS_POST) {
            $action = I('post.action');
            if ($action) {
                //添加扩展项
                if ($action == 'add') {
                    $data = array(
                        'fieldname' => trim(I('post.fieldname')),
                        'type' => trim(I('post.type')),
                        'setting' => I('post.setting'),
                        C("TOKEN_NAME") => I('post.' . C("TOKEN_NAME")),
                    );
                    if ($this->Config->extendAdd($data) !== false) {
                        $this->success('扩展配置项添加成功！', U('Config/extend'));
                        return true;
                    } else {
                        $error = $this->Config->getError();
                        $this->error($error ? $error : '添加失败！');
                    }
                }
            } else {
                //更新扩展项配置
                if ($this->Config->saveExtendConfig($_POST)) {
                    $this->success("更新成功！");
                } else {
                    $error = $this->Config->getError();
                    $this->error($error ? $error : "配置更新失败！");
                }
            }
        } else {
            $action = I('get.action');
            $db = M('ConfigField');
            if ($action) {
                if ($action == 'delete') {
                    $fid = I('get.fid', 0, 'intval');
                    if ($this->Config->extendDel($fid)) {
                        cache('Config', NULL);
                        $this->success("扩展配置项删除成功！");
                        return true;
                    } else {
                        $error = $this->Config->getError();
                        $this->error($error ? $error : "扩展配置项删除失败！");
                    }
                }
            }
            $extendList = $db->order(array('fid' => 'DESC'))->select();
            $this->assign('extendList', $extendList);
            $this->display();
        }
    }

    /**
     * 删除配置项
     */
    public function extendDel()
    {
        $action = I('post.action');
        if ($action == 'delete') {
            $fid = I('post.fid', 0, 'intval');
            if ($this->Config->extendDel($fid)) {
                cache('Config', NULL);
                $this->success("扩展配置项删除成功！");
            } else {
                $error = $this->Config->getError();
                $this->error($error ? $error : "扩展配置项删除失败！");
            }
        }
    }

}
