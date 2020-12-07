<?php
/**
 * User: jayinton
 * Date: 2020/9/21
 */

namespace app\admin\controller;


use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use think\facade\Request;

/**
 * 校验码
 *
 * @package app\admin\controller
 */
class Checkcode extends AdminController
{
    public $noNeedLogin = ['index'];

    function index()
    {
        $checkcode_type = (int) AdminConfigService::getInstance()->getConfig('checkcode_type')['data'];
        $checkcode = new \app\common\libs\checkcode\Checkcode($checkcode_type);
        //验证码类型
        $checkcode->type = strtolower(Request::param('type', 'verify'));

        //设置长度
        $codelen = intval(Request::param('type', 0));
        if ($codelen) {
            if ($codelen > 8 || $codelen < 2) {
                $codelen = 4;
            }
            $checkcode->codelen = $codelen;
        }
        //设置验证码字体大小
        $fontsize = intval(Request::param('font_size', 12));
        if ($fontsize) {
            $checkcode->fontsize = $fontsize;
        }
        //设置验证码图片宽度
        $width = intval(Request::param('width', 0));
        if ($width) {
            $checkcode->width = $width;
        }
        //设置验证码图片高度
        $height = intval(Request::param('height', 0));
        if ($height) {
            $checkcode->height = $height;
        }
        //设置背景颜色
        $background = Request::param('background', '');
        if ($background) {
            $checkcode->background = $background;
        }
        //设置字体颜色
        $fontcolor = Request::param('font_color', '');
        if ($fontcolor) {
            $checkcode->fontcolor = $fontcolor;
        }

        //显示验证码
        $refresh = Request::param('refresh', false);
        $checkcode->output($refresh);
    }

}
