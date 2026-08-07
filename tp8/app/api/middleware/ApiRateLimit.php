<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\api\middleware;

use think\facade\Cache;
use think\response\Json;
use think\cache\driver\Redis as RedisDriver;

class ApiRateLimit
{
    /**
     * 进入请求
     *
     * @param $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $action = $request->action();
        $rateLimitResult = $this->checkApiRateLimit($request, $action);
        if ($rateLimitResult !== true) {
            return $rateLimitResult;
        }

        return $next($request);
    }

    /**
     * 检测控制器的方法是否匹配
     *
     * @param $action
     * @param array $arr
     *
     * @return bool
     */
    private function checkActionMatch($action, array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        if (in_array(strtolower($action), $arr) || in_array('*', $arr)) {
            return true;
        }

        return false;
    }

    /**
     * 检测 API 速率限制
     *
     * @param $request
     * @param string $action
     *
     * @return true|Json
     */
    private function checkApiRateLimit($request, string $action)
    {
        $config = $this->resolveRateLimitConfig($request);
        if (empty($config['enabled'])) {
            return true;
        }

        $exceptActions = $config['except'] ?? [];
        if (!empty($exceptActions) && $this->checkActionMatch($action, $exceptActions)) {
            return true;
        }

        $limitActions = $config['actions'] ?? ['*'];
        if (!$this->checkActionMatch($action, $limitActions)) {
            return true;
        }

        $rule = $this->resolveRateLimitRule($action, $config);
        $maxRequests = intval($rule['max_requests'] ?? 0);
        $decaySeconds = intval($rule['decay_seconds'] ?? 0);
        if ($maxRequests <= 0 || $decaySeconds <= 0) {
            return true;
        }

        $count = $this->hitRateLimit($request, $action, $decaySeconds);
        if ($count > $maxRequests) {
            return json(createReturn(false, null, '请求过于频繁，请稍后再试', 429));
        }
        return true;
    }

    /**
     * 解析速率限制配置
     *
     * @param $request
     *
     * @return array
     */
    private function resolveRateLimitConfig($request): array
    {
        $config = $request->apiRateLimit ?? null;
        if (!is_array($config) || empty($config)) {
            // 未通过 BaseApi 注入配置，默认不启用限流
            return ['enabled' => false];
        }

        return array_merge($this->getDefaultRateLimitConfig(), $config);
    }

    /**
     * 获取默认速率限制配置
     *
     * @return array
     */
    private function getDefaultRateLimitConfig(): array
    {
        return [
            'enabled' => true,
            'actions' => ['*'],
            'except' => [],
            'max_requests' => 60,
            'decay_seconds' => 60,
            'rules' => [],
        ];
    }

    /**
     * 解析动作级防刷规则
     *
     * @param string $action
     * @param array $config
     *
     * @return array
     */
    private function resolveRateLimitRule(string $action, array $config): array
    {
        $rule = [
            'max_requests' => intval($config['max_requests'] ?? 60),
            'decay_seconds' => intval($config['decay_seconds'] ?? 60),
        ];
        $rules = $config['rules'] ?? [];
        foreach ($rules as $actionName => $actionRule) {
            if (strtolower($actionName) !== strtolower($action) || !is_array($actionRule)) {
                continue;
            }
            if (isset($actionRule['max_requests'])) {
                $rule['max_requests'] = intval($actionRule['max_requests']);
            }
            if (isset($actionRule['decay_seconds'])) {
                $rule['decay_seconds'] = intval($actionRule['decay_seconds']);
            }
            break;
        }
        return $rule;
    }

    /**
     * 累计本次请求次数
     *
     * @param $request
     * @param string $action
     * @param int $decaySeconds
     *
     * @return int
     */
    private function hitRateLimit($request, string $action, int $decaySeconds): int
    {
        $cacheKey = $this->buildRateLimitCacheKey($request, $action);
        $store = Cache::store();
        if ($store instanceof RedisDriver) {
            return $this->hitRateLimitByRedis($store, $cacheKey, $decaySeconds);
        }
        // 非 Redis 缓存驱动下，get/set 非原子操作，高并发精度可能略低
        return $this->hitRateLimitByCache($cacheKey, $decaySeconds);
    }

    /**
     * 使用 Redis 累计请求次数
     *
     * @param string $cacheKey
     * @param int $decaySeconds
     *
     * @return int
     */
    private function hitRateLimitByRedis(RedisDriver $store, string $cacheKey, int $decaySeconds): int
    {
        $handler = $store->handler();
        $driverName = Cache::getDefaultDriver();
        $prefix = (string)Cache::getStoreConfig($driverName, 'prefix', '');
        $rawKey = $prefix . $cacheKey;
        $count = intval($handler->incrby($rawKey, 1));
        if ($count === 1) {
            $handler->expire($rawKey, $decaySeconds);
        }
        return $count;
    }

    /**
     * 使用通用缓存驱动累计请求次数
     *
     * @param string $cacheKey
     * @param int $decaySeconds
     *
     * @return int
     */
    private function hitRateLimitByCache(string $cacheKey, int $decaySeconds): int
    {
        $now = time();
        $current = Cache::get($cacheKey, []);
        if (!is_array($current) || empty($current['expire_at']) || intval($current['expire_at']) <= $now) {
            Cache::set($cacheKey, [
                'count' => 1,
                'expire_at' => $now + $decaySeconds,
            ], $decaySeconds);
            return 1;
        }

        $count = intval($current['count'] ?? 0) + 1;
        $expireAt = intval($current['expire_at']);
        $ttl = max(1, $expireAt - $now);
        Cache::set($cacheKey, [
            'count' => $count,
            'expire_at' => $expireAt,
        ], $ttl);
        return $count;
    }

    /**
     * 构建防刷缓存key
     *
     * @param $request
     * @param string $action
     *
     * @return string
     */
    private function buildRateLimitCacheKey($request, string $action): string
    {
        $appName = $this->getAppName($request);
        $controller = strtolower((string)$request->controller());
        $action = strtolower($action);
        $ip = $request->ip();
        return 'api_rate_limit:' . $appName . '|' . $controller . '|' . $action . '|' . $ip;
    }

    /**
     * 获取当前应用名
     *
     * @param $request
     *
     * @return string
     */
    private function getAppName($request): string
    {
        $appName = strtolower((string)app('http')->getName());
        if (!empty($appName)) {
            return $appName;
        }

        $pathInfo = trim((string)$request->pathinfo(), '/');
        if (!empty($pathInfo)) {
            $pathParts = explode('/', $pathInfo);
            return strtolower((string)($pathParts[0] ?? 'default'));
        }

        return 'default';
    }

    /**
     * 请求返回回调
     *
     * @param \think\Response $response
     */
    public function end(\think\Response $response)
    {

    }
}
