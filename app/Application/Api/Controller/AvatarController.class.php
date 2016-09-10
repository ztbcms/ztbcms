<?php

// +----------------------------------------------------------------------
// |  获取头像
// +----------------------------------------------------------------------

namespace Api\Controller;

use Common\Controller\CMS;

class AvatarController extends CMS {

    /**
     * 根据用户uid获取系统用户头像
     * http://foo.com/api.php?m=avatar&uid=用户id
     */
    public function index() {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        $size = isset($_GET['size']) ? $_GET['size'] : 90;
        $random = isset($_GET['random']) ? $_GET['random'] : '';
        $connect = isset($_GET['connect']) ? true : false;
        if (empty($random)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Last-Modified:" . date('r'));
            header("Expires: " . date('r', time() + 86400));
        }
        $avatar_url = service("Passport")->getUserAvatar((int)$uid, (int)$size, $connect);
        header('Location: ' . $avatar_url);
    }

    /**
     * 根据邮箱地址，获取gravatar头像
     * http://www.foo.com/api.php?m=avatar&a=gravatar&email=用户邮箱
     */
    public function gravatar() {
        $id_or_email = I('get.email');
        $size = I('get.size', 96);
        $default = I('get.default');
        $alt = I('get.alt', false);
        header('Location: ' . $this->getAvatar($id_or_email, $size, $default, $alt));
    }

    /**
     *  通过用户邮箱，取得gravatar头像
     * @param int|string|object $id_or_email 一个用户ID，电子邮件地址
     * @param int               $size 头像图片的大小
     * @param string            $default 如果没有可用的头像是使用默认图像的URL
     * @param string            $alt 替代文字使用中的形象标记。默认为空白
     * @return string <img>
     */
    protected function getAvatar($id_or_email, $size = '96', $default = '', $alt = false) {
        return get_avatar($id_or_email, $size, $default, $alt);
    }

}
