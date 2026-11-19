<?php

namespace app\admin\model;

use app\admin\service\AdminUserService;
use think\Model;

/**
 * 操作日志
 *
 * @package app\admin\model
 */
class OperationlogModel extends Model
{
    /**
     * 敏感字段脱敏占位符
     */
    const SENSITIVE_MASK = '******';

    protected $name = 'operation_log';

    /**
     * 删除操作日志(X天前的数据)
     *
     * @param  int  $day  天数
     *
     * @return bool
     */
    static function deleteOperationLog($day = 30)
    {
        $limit_time = time() - $day * 24 * 60 * 60;
        OperationlogModel::where('time', '<=', $limit_time)->delete();
        return true;
    }

    /**
     * 记录操作日志
     *
     * @param  \think\Response  $response
     *
     * @return bool
     */
    static function record(\think\Response $response)
    {
        $request = request();
        if (!$request->isPost() || self::isLoginRequest($request)) {
            return false;
        }

        $content = json_decode($response->getContent(), true);
        $status = $content['status'] ?? 0;
        $adminInfo = AdminUserService::getInstance()->getInfo();
        $postParams = $request->post();
        $sanitizedParams = self::sanitizeParams(is_array($postParams) ? $postParams : []);
        $encodedParams = json_encode($sanitizedParams, JSON_UNESCAPED_UNICODE);

        $logData = [
            'uid'      => is_array($adminInfo) ? (int) ($adminInfo['id'] ?? 0) : 0,
            'status'   => $status ? 1 : 0,
            'method'   => $request->method(),
            'url'      => $request->url(),
            'params'   => $encodedParams !== false ? $encodedParams : '{}',
            'response' => $response->getContent(),
            'time'     => time(),
            'ip'       => $request->ip(),
        ];
        OperationlogModel::insert($logData);
        return true;
    }

    /**
     * 判断是否为后台登录请求（登录接口包含明文/编码密码，不写入操作日志）
     *
     * @param  mixed  $request  请求实例
     *
     * @return bool
     */
    public static function isLoginRequest($request): bool
    {
        if (!$request) {
            return false;
        }

        $controller = strtolower((string) $request->controller());
        $action = strtolower((string) $request->action());
        if (($controller === 'login' && in_array($action, ['dologin', 'index', ''], true)) || $action === 'dologin') {
            return true;
        }

        $candidates = [
            (string) $request->baseUrl(),
            (string) $request->url(),
        ];
        if (
            PHP_SAPI !== 'cli'
            || (string) $request->server('PATH_INFO', '') !== ''
            || (string) $request->server('REQUEST_URI', '') !== ''
            || isset($_GET['s'])
        ) {
            $candidates[] = (string) $request->pathinfo();
        }

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }
            foreach ([$candidate, urldecode($candidate)] as $path) {
                if ($path !== '' && preg_match('#(?:^|/|\b)login/dologin(?:\b|/|\.|\?|$)#i', $path) === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 判断参数键名是否为敏感字段
     *
     * @param  string  $key  参数键名
     *
     * @return bool
     */
    public static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(trim($key));
        if ($normalized === '') {
            return false;
        }

        $substrings = ['password', 'passwd', 'pwd', 'secret'];
        foreach ($substrings as $needle) {
            if (strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        $exactKeys = [
            'pass',
            'old_pass',
            'new_pass',
            'private_key',
            'aes_key',
            'access_token',
            'refresh_token',
        ];

        return in_array($normalized, $exactKeys, true);
    }

    /**
     * 递归脱敏请求参数中的敏感字段
     *
     * @param  mixed  $params    请求参数
     * @param  bool   $modified  是否发生脱敏替换
     *
     * @return mixed
     */
    public static function sanitizeParams($params, bool &$modified = false)
    {
        if (!is_array($params)) {
            return $params;
        }

        $sanitized = [];
        foreach ($params as $key => $value) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                if ($value !== self::SENSITIVE_MASK) {
                    $modified = true;
                }
                $sanitized[$key] = self::SENSITIVE_MASK;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeParams($value, $modified);
                continue;
            }

            if (is_string($value)) {
                $trimmed = ltrim($value);
                if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                    $decodedInner = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedInner)) {
                        $innerModified = false;
                        $sanitizedInner = self::sanitizeParams($decodedInner, $innerModified);
                        if ($innerModified) {
                            $modified = true;
                            $encodedInner = json_encode($sanitizedInner, JSON_UNESCAPED_UNICODE);
                            $sanitized[$key] = $encodedInner !== false ? $encodedInner : self::SENSITIVE_MASK;
                            continue;
                        }
                    }
                }
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * 对序列化后的操作日志参数字符串进行脱敏
     *
     * @param  string  $rawParams  原始参数 JSON 字符串
     * @param  bool    $modified   是否发生脱敏替换
     *
     * @return string
     */
    public static function sanitizeParamsString(string $rawParams, bool &$modified = false): string
    {
        if ($rawParams === '') {
            return '';
        }

        $decoded = json_decode($rawParams, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $sanitized = self::sanitizeParams($decoded, $modified);
            if (!$modified) {
                return $rawParams;
            }

            $encoded = json_encode($sanitized, JSON_UNESCAPED_UNICODE);
            return $encoded !== false ? $encoded : '{}';
        }

        if (preg_match('/(password|passwd|pwd|secret|private_key|aes_key|access_token|refresh_token)/i', $rawParams) === 1) {
            $modified = true;
            return '{}';
        }

        return $rawParams;
    }

}
