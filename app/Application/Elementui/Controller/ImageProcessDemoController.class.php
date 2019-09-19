<?php
/**
 * User: jayinton
 * Date: 2019-09-20
 * Time: 00:06
 */

namespace Elementui\Controller;


use Common\Controller\AdminBase;
use Intervention\Image\ImageManagerStatic as Image;

class ImageProcessDemoController extends AdminBase
{
    function index()
    {

    }

    function createSharePoster()
    {
        $application_id = '';
        $isFilePath = false;
        $store_member_id = 1;

        // 图片命名规则
        $file_base_path = SITE_PATH . 'd/';
        // 背景图
        $bg_img_url = SITE_PATH . 'statics/admin/demo/elementui/images/bg.png';
        $poster_file_path = 'poster/poster_' . md5($bg_img_url) . '_' . $store_member_id . '.jpg';
        $save_path = $file_base_path . $poster_file_path;
        $font_path = SITE_PATH . 'statics/admin/demo/elementui/font/fh.ttf';
        $return_url = $save_path;

        //已存在就返回
//        if (File::exists($save_path)) {
//            if (!$isFilePath) {
//                $return_url = config('app.img_host') . $poster_file_path;
//            }
//            return self::returnSuccess($return_url);
//        }

        // 画布
        $img = Image::canvas(640, 940);
//var_dump($bg_img_url);exit;
        // 背景图
        $thumb = Image::make($bg_img_url)->resize(640, 940);
        $img->insert($thumb);

        //用户头像
        $avatar_url = SITE_PATH . 'statics/admin/demo/elementui/images/avatar.jpg';
        $avatarImg = Image::make(file_get_contents($avatar_url))->resize(100, 100);
        $w = 100;
        $h = 100;
        $r = $w / 2;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $_x = $x - $w / 2;
                $_y = $y - $h / 2;
                if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
                    // 圆形内
                } else {
                    //圆形外，画空白
                    $avatarImg->pixel(array(255, 255, 255, 0), $x, $y);
                }
            }
        }
        $img->insert($avatarImg, 'top-left', 30, 670);

        //用户名称
        $nick_name = 'Jackson 杰克逊';
        $nickname_color = '#333';
        $this->_renderText($img, $nick_name, 145, 705, 28, $nickname_color, 0, 0);


        // 用户小程序二维码
        $mini_code_url = SITE_PATH . 'statics/admin/demo/elementui/images/qrcode.png';

        $qrcode = Image::make($mini_code_url)->resize(200, 200);

        if ($qrcode) {
            $img->insert($qrcode, 'top-right', 30, 700);
        }


        // 描述
        $description_color = '#333';
        $description = '我就666，不一样的烟火。今年过节不送礼礼礼礼礼礼礼礼礼礼礼礼礼礼，送礼就送XXX!';

        //最多显示2行
        $this->_renderText($img, $description, 30, 800, 24, $description_color, 360, 24 * 2, 'left', 'top', 14, 'clip');


        // 保存图片
//        var_dump();exit;
        if (!file_exists(dirname($save_path))) {
            mkdir(dirname($save_path), 0755, true);
        }
        $img->save($save_path, 90, 'jpg');

//        if (!$isFilePath) {
//            $return_url = config('app.img_host') . $poster_file_path;
//        }
        return self::createReturn(true, $save_path);
    }

    /**
     * 渲染文字
     * 好处：指定宽时，会自动换行，指定高度时，会限制最大行数
     * @param \Intervention\Image\Image $image
     * @param $text
     * @param int $start_x
     * @param int $start_y
     * @param int $font_size
     * @param string $font_color
     * @param int $width 0时不限制单行字数
     * @param int $height 0时不限制行数
     * @param string $align
     * @param string $valign
     * @param int $marginTop 每行外边距离
     * @param string $textOverflow 文本溢出时处理 ellipsis 省略号 clip 裁剪
     */
    private function _renderText(\Intervention\Image\Image $image, $text, $start_x = 0, $start_y = 0, $font_size = 16, $font_color = '#333',
                                 $width = 0, $height = 0, $align = 'left', $valign = 'top', $marginTop = 0, $textOverflow = 'ellipsis')
    {
        $font_path = SITE_PATH . 'statics/admin/demo/elementui/font/fh.ttf';
        //单行最大数量
        $max_single_line_text_amount = mb_strlen($text) * 1000;// 默认无限
        if ($width != 0) {
            //向下取整
            $max_single_line_text_amount = floor($width / $font_size);
        }
        //文字最大行数
        $max_line_amount = mb_strlen($text) * 1000;
        if ($height != 0) {
            //向上取整
            $max_line_amount = ceil($height / $font_size);
        }
        //渲染
        for ($i = 0; $i < $max_line_amount; $i++) {
            if (empty($text)) {
                break;
            }
            if (mb_strlen($text) <= $max_single_line_text_amount) {
                $sub_text = $text;
            } else {
                $sub_text = mb_substr($text, 0, $max_single_line_text_amount, 'utf-8');
            }

            if (!empty($sub_text)) {
                //处理最后一行时文本溢出
                if ($i == $max_line_amount - 1 && mb_strlen($text) - $max_single_line_text_amount > 0) {
                    if ($textOverflow == 'ellipsis') {
                        //省略号
                        $sub_text = mb_substr($sub_text, 0, mb_strlen($sub_text) - 3, 'utf-8');
                        $sub_text .= '...';
                    }
                }

                $image->text($sub_text, $start_x, $start_y + $i * ($font_size + $marginTop), function ($font) use ($font_size, $font_color, $font_path, $align, $valign)
                {
                    $font->file($font_path);
                    $font->color($font_color);
                    $font->size($font_size);
                    $font->align($align);
                    $font->valign($valign);
                });

                //处理下一段
                $text = mb_substr($text, $max_single_line_text_amount, mb_strlen($text) - $max_single_line_text_amount, 'utf-8');
            }
        }

    }
}