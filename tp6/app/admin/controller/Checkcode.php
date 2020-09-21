<?php
/**
 * User: jayinton
 * Date: 2020/9/21
 */

namespace app\admin\controller;


use app\admin\service\AdminConfigService;
use app\BaseController;
use think\facade\Request;

class Checkcode extends BaseController
{
    public function index() {
        $checkcode_type = (int)AdminConfigService::getInstance()->getConfig('checkcode_type');
        $checkcode = new \app\common\libs\checkcode\Checkcode($checkcode_type);
        //验证码类型
//        $checkcode->type = I('get.type', 'verify', 'strtolower');
        $checkcode->type =  strtolower(Request::param('type', 'verify'));
        //设置长度
//        $codelen = I('get.code_len', 0, 'intval');
        $codelen = intval(Request::param('type', 0));
        if ($codelen) {
            if ($codelen > 8 || $codelen < 2) {
                $codelen = 4;
            }
            $checkcode->codelen = $codelen;
        }
        //设置验证码字体大小
//        $fontsize = I('get.font_size', 0, 'intval');
        $fontsize = intval(Request::param('font_size', 12));
        if ($fontsize) {
            $checkcode->fontsize = $fontsize;
        }
        //设置验证码图片宽度
//        $width = I('get.width', 0, 'intval');
        $width = intval(Request::param('width', 0));
        if ($width) {
            $checkcode->width = $width;
        }
        //设置验证码图片高度
//        $height = I('get.height', 0, 'intval');
        $height = intval(Request::param('height', 0));
        if ($height) {
            $checkcode->height = $height;
        }
        //设置背景颜色
//        $background = I('get.background', '', '');
        $background = Request::param('background', '');
        if ($background) {
            $checkcode->background = $background;
        }
        //设置字体颜色
//        $fontcolor = I('get.font_color', '', '');
        $fontcolor = Request::param('font_color', '');
        if ($fontcolor) {
            $checkcode->fontcolor = $fontcolor;
        }

        //显示验证码
        $refresh = Request::param('refresh', false);
        $checkcode->output($refresh);
    }
}