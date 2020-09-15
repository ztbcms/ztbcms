<?php
/**
 * User: jayinton
 * Date: 2019-09-20
 * Time: 00:06
 */

namespace Elementui\Controller;


use Common\Controller\AdminBase;
use Intervention\Image\ImageManagerStatic as Image;
use Log\Service\LogService;

class ImageProcessDemoController extends AdminBase
{
    function index()
    {
        $this->display();
    }

    /**
     * 图片生成建议：
     * 1. 少用字符
     * 2. 文字尽量简洁
     * 3. 不支持emoji
     */
    function createSharePoster()
    {
        $nickname = I('nick_name');
        $description = I('description');
        if (empty($nickname)) {
            $nickname = 'Jackson 杰克逊';
        }
        if (empty($description)) {
            $description = '我就666，不一样的烟火。今年过节不送礼，送礼就送XXX!';
        }
        $config = [
            'nick_name' => $this->_filter_special_char($nickname),
            'nickname_color' => '#333',
            'description_color' => '#333',
            'description' => $this->_filter_special_char($description),
        ];
        $this->ajaxReturn($this->_genImage($config));
    }

    private function _genImage($config)
    {
        // 图片命名规则
        $upload_dir = 'd/file/';
        $file_base_path = SITE_PATH . $upload_dir;
        $poster_file_name = '';
        foreach ($config as $key => $value) {
            $poster_file_name .= $key . $value;
        }
        $poster_file_name = md5($poster_file_name . 'v1');
        // 背景图
        $bg_img_url = SITE_PATH . 'statics/admin/demo/elementui/images/bg.png';
        $poster_file_path = 'poster/' . $poster_file_name . '.jpg';
        $save_path = $file_base_path . $poster_file_path;
        $return_path = '/' . $upload_dir . $poster_file_path;

        //已存在就返回
        if (file_exists($save_path)) {
            return self::createReturn(true, ['path' => $return_path]);
        }

        // 画布
        $img = Image::canvas(640, 940);

        // 背景图
        $thumb = Image::make($bg_img_url)->resize(640, 940);
        $img->insert($thumb);

        //用户头像
        $avatar_url = SITE_PATH . 'statics/admin/demo/elementui/images/avatar.jpg';
        $w = 100;
        $h = 100;
        $avatarImg = Image::make(file_get_contents($avatar_url))->resize($w, $h);
        $this->_roundImage($avatarImg, $w, $h);
        $img->insert($avatarImg, 'top-left', 30, 670);

        //用户名称
        $nick_name = $config['nick_name'];
        $nickname_color = $config['nickname_color'];
        $this->_renderText($img, $nick_name, 145, 705, 28, $nickname_color, 290, 28);

        // 用户小程序二维码
        $mini_code_url = SITE_PATH . 'statics/admin/demo/elementui/images/qrcode.png';

        $qrcode = Image::make($mini_code_url)->resize(200, 200);

        if ($qrcode) {
            $img->insert($qrcode, 'top-right', 30, 700);
        }

        // 描述
        $description_color = $config['description_color'];
        $description = $config['description'];

        //最多显示2行
        $this->_renderText($img, $description, 30, 800, 24, $description_color, 360, 24 * 2, 'left', 'top', 14, 'ellipsis');

        // 保存图片
        if (!file_exists(dirname($save_path))) {
            mkdir(dirname($save_path), 0755, true);
        }
        $img->save($save_path, 90, 'jpg');

        return self::createReturn(true, ['path' => $return_path]);
    }

    /**
     * 把图片切为圆形
     * @param \Intervention\Image\Image $image
     * @param int $width
     * @param int $height
     */
    private function _roundImage(\Intervention\Image\Image $image, $width = 100, $height = 100)
    {
        $w = $width;
        $h = $height;
        $r = $w / 2;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $_x = $x - $w / 2;
                $_y = $y - $h / 2;
                if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
                    // 圆形内
                } else {
                    //圆形外，画空白
                    $image->pixel(array(255, 255, 255, 0), $x, $y);
                }
            }
        }
    }

    /**
     * 渲染文字
     * 特点：
     * 1. 指定宽时，会自动换行，
     * 2. 指定高度时，会限制最大行数
     * 3. 内容过长时，会友好地加入省略号
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
     * @param string $textOverflowEllipsis
     */
    private function _renderText(\Intervention\Image\Image $image, $text, $start_x = 0, $start_y = 0, $font_size = 16, $font_color = '#333',
                                 $width = 0, $height = 0, $align = 'left', $valign = 'top', $marginTop = 0, $textOverflow = 'ellipsis', $textOverflowEllipsis = '...')
    {
        //引入字体
        $font_path = SITE_PATH . 'statics/admin/demo/elementui/font/SourceHanSansCN-Regular.otf';
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
            $sub_text = $this->_getLineText($text, $font_size, $width);

            if (!empty($sub_text)) {
                //处理最后一行时文本溢出
                if ($i == $max_line_amount - 1) {
                    $is_overflow = mb_strlen($text) - mb_strlen($sub_text) > 0;
                    if ($is_overflow && $textOverflow == 'ellipsis') {
                        //省略号
                        $sub_text = $this->appentTextOverflowEllipsis($sub_text, $textOverflowEllipsis, $font_size, $width);
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

    /**
     * 统计字体类型
     * @param $string
     * @return array
     */
    function _calTextTypeInfo($string)
    {
        $len = mb_strlen($string);
        $en_amount = 0;
        $cn_amount = 0;
        $special_amount = 0;
        for ($i = 0; $i < $len; $i++) {
            $str = mb_substr($string, $i, 1);
            $str_len = strlen($str);
            if ($str_len == 1) {
                //英文、符号
                $en_amount++;
            } else if ($str_len >= 2 && $str_len <= 3) {
                //中文
                $cn_amount++;
            } else {
                //字符为4个位以上就是特殊字符如emoji
                $special_amount++;
            }
        }
        return [
            'en_amount' => $en_amount,
            'cn_amount' => $cn_amount,
            'special_amount' => $special_amount,
        ];
    }

    /**
     * 过滤字符，保留中英文、符号
     * @param $string
     * @return string
     */
    function _filter_special_char($string)
    {
        $return = '';
        $len = mb_strlen($string);

        for ($i = 0; $i < $len; $i++) {
            $str = mb_substr($string, $i, 1);

            $str_len = strlen($str);
            if ($str_len == 1) {
                //英文、符号
                $return .= $str;
            } else if ($str_len >= 2 && $str_len <= 3) {
                //中文
                $return .= $str;
            } else {
                //字符为4个位以上就是特殊字符如emoji
            }

        }

        return $return;
    }

    /**
     * 获取字体宽度
     * @param $text
     * @param $font_size
     * @return float|int
     */
    function _get_text_width($text, $font_size)
    {
        $text_info = $this->_calTextTypeInfo($text);
        $width = $text_info['en_amount'] * $font_size / 2 + $text_info['cn_amount'] * $font_size;
        return $width;
    }

    /**
     * 根据给定的最大的宽度，尽可能获取文字
     * @param $given_text
     * @param int $font_size
     * @param int $max_width
     * @return string
     */
    function _getLineText($given_text, $font_size = 16, $max_width = 0)
    {
        $result_text = '';
        if ($max_width == 0) {
            $result_text = $given_text;
        } else {
            $i = 0;
            while ($i < mb_strlen($given_text)) {
                $next_str = $result_text . mb_substr($given_text, $i, 1);
                $width = $this->_get_text_width($next_str, $font_size);
                if ($width <= $max_width) {
                    $result_text = $next_str;
                    $i++;
                } else {
                    break;
                }
            }
        }

        return $result_text;
    }

    /**
     * 在文本后面添加上省略号
     * @param string $str 原文本
     * @param string $textOverflowEllipsis
     * @param int $font_size 字体大小
     * @param int $max_width 最大宽度
     * @return string
     */
    function appentTextOverflowEllipsis($str, $textOverflowEllipsis = '...', $font_size = 16, $max_width = 0)
    {
        if ($max_width == 0) {
            return $str . $textOverflowEllipsis;
        }
        $ellipsis_width = $this->_get_text_width($textOverflowEllipsis, $font_size);
        $width = $this->_get_text_width($str, $font_size);
        $i = 0;
        while ($width + $ellipsis_width > $max_width) {
            $str = mb_substr($str, 0, mb_strlen($str) - 1);
            if(empty($str)){
                break;
            }
            $width = $this->_get_text_width($str, $font_size);
            $i++;

        }
        return $str . $textOverflowEllipsis;
    }

    //居中裁剪
    //参考：https://www.cnblogs.com/woider/p/6380491.html
    /**
     * 图片尺寸变形(resize)，若尺寸比例不一致时，居中缩放
     * @param  \Intervention\Image\Image  $source_image
     * @param int $width 目标宽度
     * @param t $height 目标长度
     *
     * @return \Intervention\Image\Image
     */
    function  _image_center_crop(\Intervention\Image\Image $source_image, $width, $height)
    {

        /* 获取图像尺寸信息 */
        $target_w = intval($width);
        $target_h = intval($height);
        $source_w = $source_image->width();
        $source_h = $source_image->height();
        /* 计算裁剪宽度和高度 */
        $judge = (($source_w / $source_h) > ($target_w / $target_h));
        $resize_w = $judge ? intval(($source_w * $target_h) / $source_h) : $target_w;
        $resize_h = !$judge ? intval(($source_h * $target_w) / $source_w) : $target_h;
        $start_x = $judge ? intval(($resize_w - $target_w) / 2) : 0;
        $start_y = !$judge ? intval($resize_h - $target_h) / 2 : 0;
        /* 绘制居中缩放图像 */
        return $source_image->crop($resize_w, $resize_h, $start_x, $start_y)->resize($target_w, $target_w);
    }
}