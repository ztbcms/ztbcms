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
     *  上传
     */
    public function upload() {
        if (IS_POST) {
//            $sess_id = I("post.sessid", 0);
//            $key = md5(C("AUTHCODE") . $sess_id . self::isadmin);
//            密钥验证
//            if (I("post.swf_auth_key") != $key) {
//                exit("0,权限认证失败！" . I("post.swf_auth_key") . "|" . C("AUTHCODE"));
//            }
            //回调函数
            $Callback = false;
            //用户ID
//            $upuserid = I('post.uid', 0, 'intval');
            $userInfo = User::getInstance()->getInfo();
            $upuserid = $userInfo['id'];
            //用户组
//            $groupid = I("post.groupid", 8, "intval");
//            $Member_group = cache("Member_group");
//            if ((int) $Member_group[$groupid]['allowattachment'] < 1) {
//                exit("0,所在的用户组没有附件上传权限！");
//            }
            //取得栏目ID
            $catid = I('post.catid', 0, 'intval');
            //取得模块名称
            $module = I('post.module', 'module_transport', 'trim,strtolower');
            //获取附件服务
            $Attachment = service("Attachment", array('module' => $module, 'catid' => $catid, 'isadmin' => self::isadmin, 'userid' => $upuserid));

            //开始上传
            $info = $Attachment->upload($Callback);
            if ($info) {
                if (in_array(strtolower($info[0]['extension']), array("jpg", "png", "jpeg", "gif"))) {
                    // 附件ID 附件网站地址 图标(图片时为1) 文件名
                    $this->ajaxReturn([
                        'status' => true,
                        'data' => [
                            'aid' => $info[0]['aid'],
                            'url' => $info[0]['url'],
                            'name' => $info[0]['name'],
                        ],
                        'msg' => '上传成功'
                    ]);
                    return;
                } else {
                    $fileext = $info[0]['extension'];
                    if ($fileext == 'zip' || $fileext == 'rar') {
                        $fileext = 'rar';
                    } elseif ($fileext == 'doc' || $fileext == 'docx') {
                        $fileext = 'doc';
                    } elseif ($fileext == 'xls' || $fileext == 'xlsx') {
                        $fileext = 'xls';
                    } elseif ($fileext == 'ppt' || $fileext == 'pptx') {
                        $fileext = 'ppt';
                    } elseif ($fileext == 'flv' || $fileext == 'swf' || $fileext == 'rm' || $fileext == 'rmvb') {
                        $fileext = 'flv';
                    } else {
                        $fileext = 'do';
                    }

                    $this->ajaxReturn([
                        'status' => true,
                        'data' => [
                            'aid' => $info[0]['aid'],
                            'url' => $info[0]['url'],
                            'name' => $info[0]['name'],
                        ],
                        'msg' => '上传成功'
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