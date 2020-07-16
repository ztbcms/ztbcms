<?php
/**
 * User: jayinton
 * Date: 2020/7/16
 * Time: 下午3:37
 */

namespace Common\Service;

use System\Service\BaseService;

/**
 * 请求缓存服务
 * 适用于接口
 *
 * @package Common\Service
 */
class RequestCacheService extends BaseService
{
    // 路由key的版本号后缀
    const ROUTER_KEY_VERSION_SUFFIX = 'updated_at';

    static function generateRouteKey($module, $controller, $action)
    {
        return strtolower($module.'/'.$controller.'/'.$action);
    }

    /**
     * 生成路由的缓存key
     *
     * @param $route
     * @param  array  $param_data
     *
     * @param  array  $rule
     *
     * @return string
     */
    static function generateRouteCacheKey($route, $param_data = [], $rule = [])
    {
        //key=action+md5(params)
        $params = '';
        foreach ($param_data as $k => $v) {
            $params .= strtolower($k.'='.$v);
        }

        // 处理订阅
        if ($rule && isset($rule['subscribe'])) {
            foreach ($rule['subscribe'] as $index => $value) {
                $v = cache(self::generateRouterVersionKey($value)) ?: '';
                $params .= self::generateRouterVersionKey($value).'='.$v;
            }
        }
        return $route.':'.md5($params);
    }

    static function generateRouterVersionKey($route)
    {
        return strtolower($route).':'.self::ROUTER_KEY_VERSION_SUFFIX;
    }

    private function makeRouteKey()
    {
        return self::generateRouteKey(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
    }

    /**
     * 生成缓存key
     *
     * @return string
     */
    private function makeRouteCacheKey()
    {
        $param_data = I('');
        $route = self::generateRouteKey(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
        $rule = $this->getRuleByRouter($route);
        return self::generateRouteCacheKey($route, $param_data, $rule);
    }

    /**
     * 设置缓存
     *
     * @param  array  $data
     * @param  null  $expire
     * @param  string  $ajax_return_type
     *
     * @return mixed
     */
    function setCacheData(array $data, $expire = null, $ajax_return_type = '')
    {
        if (empty($expire)) {
            $expire = C('REQUEST_CACHE_TIME');
        }
        if (empty($ajax_return_type)) {
            $ajax_return_type = C('DEFAULT_AJAX_RETURN');
        }

        $cache_data = [
            'key'    => $this->makeRouteCacheKey(),
            'type'   => $ajax_return_type,
            'expire' => NOW_TIME + intval($expire),
            'data'   => $data
        ];
        $route_key = $this->makeRouteKey();
        $route_version_key = self::generateRouterVersionKey($route_key);
        // 缓存数据
        cache($cache_data['key'], json_encode($cache_data), ['expire' => $expire]);
        // 路由的最后更新时间
        cache($route_version_key, NOW_TIME);
        return true;
    }

    /**
     * 读取缓存
     *
     * @param  null  $default
     *
     * @return mixed|null
     */
    function getCacheData($default = null)
    {
        $cache = cache($this->makeRouteCacheKey());
        if ($cache) {
            return json_decode($cache, true);
        }
        return $default;
    }

    function getRuleByRouter($route)
    {
        $route = strtolower($route);
        // 读取静态规则
        $rules = C('REQUEST_CACHE_RULES');
        $rule = null;
        if (!empty($rules)) {
            $rule_map = C('REQUEST_CACHE_RULES'.'CACHE');
            if (empty($rule_map)) {
                foreach ($rules as $rule_name => $v) {
                    $rule_map[strtolower($rule_name)] = $v;
                }
                C('REQUEST_CACHE_RULES'.'CACHE', $rule_map);
            }

            if (isset($rule_map[$route])) {
                $rule = $rule_map[$route];
            } else {
                if (isset($rules['*'])) {
                    $rule = $rule_map['*'];
                }
            }
        }
        if ($rule && !isset($rule['expire'])) {
            $rule['expire'] = C('REQUEST_CACHE_TIME');
        }

        return $rule;
    }

    /**
     * 判断是否需要请求缓存
     *
     */
    function enableRequestCache()
    {
        $route = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        // 读取静态规则
        $rule = $this->getRuleByRouter($route);

        if ($rule) {
            return self::createReturn(true, $rule);
        }
        return self::createReturn(false, null);;
    }

    /**
     * 返回内容
     *
     * @param $data array|null 数据
     * @param  string  $type  ajax 返回类型
     */
    function ajaxReturn($data, $type = '')
    {
        if (empty($type)) {
            $type = C('DEFAULT_AJAX_RETURN');
        }
        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:text/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            default:
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:text/json; charset=utf-8');
                exit(json_encode($data));
        }
    }
}