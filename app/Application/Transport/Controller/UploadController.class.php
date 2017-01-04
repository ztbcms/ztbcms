<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Admin\Service\User;
use Common\Controller\AdminBase;

class UploadController extends AdminBase  {

    const isadmin = 1; //后台上传

    /**
     *  上传Excel文件操作
     */
    public function upload() {
        if (IS_POST) {
            //回调函数
            $Callback = false;
            $userInfo = User::getInstance()->getInfo();
            $upuserid = $userInfo['id'];
            //取得栏目ID
            $catid = I('post.catid', 0, 'intval');
            //取得模块名称
            $module = I('post.module', 'module_transport', 'trim,strtolower');
            //获取附件服务
            $Attachment = service("Attachment", array('module' => $module, 'catid' => $catid, 'isadmin' => self::isadmin, 'userid' => $upuserid));

            //开始上传
            $info = $Attachment->upload($Callback);
            if ($info) {
                $fileext = $info[0]['extension'];
                if ($fileext == 'xls' || $fileext == 'xlsx') {
                    $fileext = 'xls';
                    $this->ajaxReturn([
                        'status' => true,
                        'data' => [
                            'aid' => $info[0]['aid'],
                            'url' => $info[0]['url'],
                            'name' => $info[0]['name'],
                        ],
                        'msg' => '上传成功'
                    ]);
                } else{
                    $this->ajaxReturn([
                        'status' => false,
                        'data' => null,
                        'msg' => '请上传.xls或.xlsx文件'
                    ]);
                }

            } else {
                //上传失败，返回错误
                $this->ajaxReturn([
                    'status' => false,
                    'msg' => $Attachment->getErrorMsg()
                ]);
            }
        } else {
            $this->ajaxReturn([
                'status' => false,
                'msg' => '上传失败'
            ]);
        }
    }

}