<?php
/**
 * User: jayinton
 * Date: 2019/12/10
 * Time: 22:48
 */

namespace Common\Traits\Crypto;


use Common\Service\CryptoService;
use Think\Controller;

/**
 *
 * 后台渲染，key 存在session, 同时也返回给前端,来源：自动生成
 * api,模式，key=授权得到token来
 * 1. 初始化token,
 * Trait CryptControllerTrait
 * @package Common\Traits\Crypto
 */
trait CryptoControllerTrait
{
    protected $key = '';
    private $SESSION_KEY_CRYPTO_KEY = '__ztbcms_crypto_key__';

    function initCryptoKey($init_key = '')
    {
        if (!empty($init_key)) {
            $this->setCryptoKey($init_key);
        } else {
            $key = session($this->SESSION_KEY_CRYPTO_KEY);
            if (empty($key)) {
                $this->resetCryptoKey();
            } else {
                $this->setCryptoKey($key);
            }
        }
    }

    function resetCryptoKey()
    {
        $this->setCryptoKey(uniqid());
    }

    function setCryptoKey($key)
    {
        $this->key = $key;
        session($this->SESSION_KEY_CRYPTO_KEY, $key);
    }

    function getCryptoKey()
    {
        return $this->key;
    }

    /**
     * 构建加密的返回格式
     * @param array $data
     * @return array
     */
    function makeEncryptReturn(array $data)
    {
        $json_data = json_encode($data);
        $encrypt_data = CryptoService::encrypt($json_data, $this->getCryptoKey());
        return [
            'data' => $data
        ];
    }

    /**
     * 解密json格式信息
     * @param $cipher_text
     * @return mixed
     */
    function decryptJsonString($cipher_text)
    {
        $dencrypt_data = CryptoService::decrypt($cipher_text, $this->getCryptoKey());
        return json_decode($dencrypt_data, 1);
    }


}