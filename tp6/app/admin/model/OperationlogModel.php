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
            'url'      => self::sanitizeUrl((string) $request->url(), $request),
            'params'   => $encodedParams !== false ? $encodedParams : '{}',
            'response' => $response->getContent(),
            'time'     => time(),
            'ip'       => $request->ip(),
        ];
        OperationlogModel::insert($logData);
        return true;
    }

    /**
     * 清洗操作日志记录的 URL，剥离查询字符串与操作名之后的额外路由参数，仅保留应用/控制器/操作标识及 s 路由模式标识
     *
     * @param  string  $rawUrl   原始 URL
     * @param  mixed   $request  可选请求实例（用于辅助定位控制器/操作及 rawUrl 为空时兜底获取 baseUrl）
     *
     * @return string
     */
    public static function sanitizeUrl(string $rawUrl, $request = null): string
    {
        if ($rawUrl === '' && $request) {
            $rawUrl = (string) $request->baseUrl();
        }
        if ($rawUrl === '') {
            return '';
        }

        $cleanUrl = explode('#', $rawUrl, 2)[0];
        if (strpos($cleanUrl, '?') === false) {
            return self::normalizeRoutePath($cleanUrl, $request);
        }

        [$pathPart, $queryString] = explode('?', $cleanUrl, 2);
        $sRoute = self::extractSRouteFromQuery($queryString);
        if ($sRoute === '' && $request && method_exists($request, 'get')) {
            $reqS = $request->get('s');
            if (is_string($reqS) && trim($reqS) !== '') {
                $sRoute = self::cleanRouteSegment($reqS);
            }
        }

        if ($sRoute !== '') {
            return $pathPart . '?s=' . self::normalizeRoutePath($sRoute, $request);
        }

        return self::normalizeRoutePath($pathPart, $request);
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

        $controller = strtolower(trim((string) $request->controller()));
        $action = strtolower(trim((string) $request->action()));
        if ($controller !== '') {
            return $controller === 'login' && in_array($action, ['dologin', 'index', ''], true);
        }
        if ($action !== '') {
            return $action === 'dologin';
        }

        $routePath = self::extractRoutePath($request);
        if ($routePath === '') {
            return false;
        }

        foreach ([$routePath, urldecode($routePath)] as $path) {
            if ($path !== '' && preg_match('#(?:^|/|\b)login/dologin(?:\b|/|\.|$)#i', $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 从未解析路由的请求中提取纯路由路径（不含无关查询参数与操作名后额外路由参数）
     *
     * @param  mixed  $request  请求实例
     *
     * @return string
     */
    private static function extractRoutePath($request): string
    {
        $url = (string) $request->url();
        if ($url !== '' && strpos($url, '?') !== false) {
            [, $queryString] = explode('?', explode('#', $url, 2)[0], 2);
            $sRoute = self::extractSRouteFromQuery($queryString);
            if ($sRoute !== '') {
                return self::normalizeRoutePath($sRoute, $request);
            }
        }

        if (method_exists($request, 'get')) {
            $reqS = $request->get('s');
            if (is_string($reqS) && trim($reqS) !== '') {
                return self::normalizeRoutePath($reqS, $request);
            }
        }

        $candidates = [
            (string) $request->baseUrl(),
            $url,
            (string) $request->server('PATH_INFO', ''),
        ];
        foreach ($candidates as $candidate) {
            if ($candidate !== '') {
                return self::normalizeRoutePath($candidate, $request);
            }
        }

        return '';
    }

    /**
     * 规范化路由路径：仅保留应用/控制器/操作标识，截断操作名之后的额外路由参数段
     *
     * @param  string  $routePath  原始路由路径
     * @param  mixed   $request    可选请求实例
     *
     * @return string
     */
    private static function normalizeRoutePath(string $routePath, $request = null): string
    {
        $clean = self::cleanRouteSegment($routePath);
        if ($clean === '') {
            return '';
        }

        $hasLeadingSlash = ($clean[0] === '/');
        $segments = array_values(array_filter(explode('/', trim($clean, '/')), 'strlen'));
        if (empty($segments)) {
            return $hasLeadingSlash ? '/' : '';
        }

        $scriptPrefix = '';
        if (stripos($segments[0], '.php') !== false) {
            $scriptPrefix = array_shift($segments);
            if (empty($segments)) {
                return ($hasLeadingSlash ? '/' : '') . $scriptPrefix;
            }
        }

        if ($request) {
            $controller = strtolower(trim((string) $request->controller()));
            $action = strtolower(trim((string) $request->action()));
            if ($controller !== '' && $action !== '') {
                $maxIndex = min(2, count($segments) - 2);
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $segCtrl = strtolower($segments[$i]);
                    $segActionRaw = strtolower($segments[$i + 1]);
                    $segActionNoExt = strtolower((string) pathinfo($segments[$i + 1], PATHINFO_FILENAME));
                    if ($segCtrl === $controller && ($segActionRaw === $action || $segActionNoExt === $action)) {
                        $segments = array_slice($segments, 0, $i + 2);
                        if ($scriptPrefix !== '') {
                            array_unshift($segments, $scriptPrefix);
                        }
                        return ($hasLeadingSlash ? '/' : '') . implode('/', $segments);
                    }
                }
            }
        }

        $origCount = count($segments);
        if ($origCount >= 4) {
            $thirdSeg = strtolower((string) pathinfo($segments[2], PATHINFO_FILENAME));
            $singleAppParamKeys = [
                'password',
                'passwd',
                'pwd',
                'old_password',
                'new_password',
                'new_pwdconfirm',
                'pwdconfirm',
                'repassword',
                'confirm_password',
                'secret',
                'pass',
                'old_pass',
                'new_pass',
                'private_key',
                'aes_key',
                'access_token',
                'refresh_token',
            ];
            if (in_array(strtolower($segments[2]), $singleAppParamKeys, true) || in_array($thirdSeg, $singleAppParamKeys, true)) {
                $segments = array_slice($segments, 0, 2);
            }
        }

        if (count($segments) > 3) {
            $segments = array_slice($segments, 0, 3);
        }

        if ($scriptPrefix !== '') {
            array_unshift($segments, $scriptPrefix);
        }

        return ($hasLeadingSlash ? '/' : '') . implode('/', $segments);
    }

    /**
     * 从查询字符串中提取 s 路由标识
     *
     * @param  string  $queryString  查询字符串
     *
     * @return string
     */
    private static function extractSRouteFromQuery(string $queryString): string
    {
        if ($queryString === '') {
            return '';
        }

        $queryParams = [];
        parse_str($queryString, $queryParams);
        if (isset($queryParams['s']) && is_string($queryParams['s']) && trim($queryParams['s']) !== '') {
            return self::cleanRouteSegment($queryParams['s']);
        }

        return '';
    }

    /**
     * 截断路由段中的查询字符串与锚点
     *
     * @param  string  $route  原始路由段
     *
     * @return string
     */
    private static function cleanRouteSegment(string $route): string
    {
        $parts = preg_split('/[?&#]/', trim($route), 2);
        return is_array($parts) ? ($parts[0] ?? '') : trim($route);
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
