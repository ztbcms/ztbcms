<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\common\service\jwt;

use app\common\service\BaseService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\Exception;

/**
 * Jwt服务
 */
class JwtService extends BaseService
{
    private $config = null;

    public function __construct($scene = 'default')
    {
        $scenes = config('jwt.scene');
        throw_if(!isset($scenes[$scene]), new Exception('Not found scene:' . $scene));
        $this->config = $scenes['default'];
        if ($scene == 'default') {
            $this->config = array_merge($scenes['default'], $scenes[$scene]);
        }
    }

    /**
     * 创建jwt
     * @param array $payload
     * @return string
     */
    function createToken(array $payload = [])
    {
        return JWT::encode($payload, $this->config['secret_key'], $this->config['algorithm']);
    }

    /**
     * 解析jwt
     * @param $token
     * @return array
     */
    function parserToken($token)
    {
        try {
            $info = JWT::decode($token, new Key($this->config['secret_key'], $this->config['algorithm']));
            return self::createReturn(true, $info, '认证通过');
        } catch (\Exception $exception) {
            return self::createReturn(false, null, '认证失败：凭证无效');
        }
    }
}