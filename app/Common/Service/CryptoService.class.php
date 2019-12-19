<?php
/**
 * User: jayinton
 * Date: 2019/12/10
 * Time: 16:22
 */

namespace Common\Service;

use System\Service\BaseService;

/**
 * 加密服务
 * @package Common\Service
 */
class CryptoService extends BaseService
{

    /**
     * 字符串转16进制
     * @param $string
     * @return string
     */
    static function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    /**
     * 十六进制转字符
     * @param $hex
     * @return string
     */
    static function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    /**
     * 补零
     * AES,CBC 模式需要固定加密内容长度
     * ZeroPadding
     * @param $data
     * @return string
     */
    static function _pad_zero($data)
    {
        $len = 16;
        if (strlen($data) % $len) {
            $padLength = $len - strlen($data) % $len;
            $data .= str_repeat("\0", $padLength);
        }
        return $data;
    }

    /**
     * 加密
     * @param string $plain_text 原文
     * @param string $secret 密钥
     * @return string
     */
    static function encrypt($plain_text, $secret)
    {
        $method = 'AES-128-CBC';
        $iv_size = openssl_cipher_iv_length($method);
        $key = substr(md5($secret), 0, $iv_size);
        $iv = substr(md5($secret), 0, $iv_size);
        $encrypt_result = openssl_encrypt(self::_pad_zero($plain_text), $method, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        return self::strToHex($encrypt_result);
    }


    /**
     * 解密
     * @param string $cipher_text 密文
     * @param string $secret 密钥
     * @return false|string 成功时返回解密内容字符串
     */
    static function decrypt($cipher_text, $secret)
    {
        $method = 'AES-128-CBC';
        $iv_size = openssl_cipher_iv_length($method);
        $key = substr(md5($secret), 0, $iv_size);
        $iv = substr(md5($secret), 0, $iv_size);
        $cipher_text = self::hexToStr($cipher_text);
        $result = openssl_decrypt($cipher_text, $method, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        //去除增加的空字符
        $result = rtrim($result,"\0");
        return $result;
    }
}