<?php
/**
 * User: jayinton
 */

namespace app\common\libs\helper;


/**
 * 字符串助手类
 * Class StringHelper
 *
 * @package app\admin\libs\helper
 */
class StringHelper
{
    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     *
     * @param  int  $len  产生字符串的长度
     *
     * @return string 随机字符串
     */
    static function genRandomString($len = 6)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9",
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }
}