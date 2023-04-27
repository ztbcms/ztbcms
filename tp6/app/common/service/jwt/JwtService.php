<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\common\service\jwt;

use app\common\service\BaseService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Jwt服务
 */
class JwtService extends BaseService
{
    /**
     * 创建jwt
     * @param array $payload
     * @return array
     */
    function createToken(array $payload = [])
    {
        $config = config('api.jwt');
        $token = JWT::encode($payload, $config['secret_key'], $config['algorithm']);
        return self::createReturn(true, ['token' => $token], '创建成功');
    }

    /**
     * 解析jwt
     * @param $token
     * @return array
     */
    function parserToken($token)
    {
        $config = config('api.jwt');
        try {
            $info = JWT::decode($token, new Key($config['secret_key'], $config['algorithm']));
            return self::createReturn(true, $info, '认证通过');
        } catch (\Exception $exception) {
            return self::createReturn(false, null, '认证失败：凭证无效');
        }
    }
}