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

    /**
     * 生成路由key
     * @param $module
     * @param $controller
     * @param $action
     *
     * @return string
     */
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
        //key升序排序
        ksort($param_data);
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

    /**
     * 生成路由版本key
     * @param $route
     *
     * @return string
     */
    static function generateRouterVersionKey($route)
    {
        return strtolower($route).':'.self::ROUTER_KEY_VERSION_SUFFIX;
    }

    /**
     * 创建路由key
     * @return string
     */
    function makeRouteKey()
    {
        return self::generateRouteKey(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
    }

    /**
     * 生成缓存key
     *
     * @return string
     */
    function makeRouteCacheKey()
    {
        $param_data = I('');
        $route = self::generateRouteKey(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
        $rule = $this->getRuleByRoute($route);
        return self::generateRouteCacheKey($route, $param_data, $rule);
    }

    /**
     * 设置缓存
     *
     * @param  string  $data
     * @param  null  $expire
     * @param  string  $ajax_return_type
     *
     * @return mixed
     */
    function setCacheData($data, $expire = null, $ajax_return_type = '')
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
        cache($cache_data['key'], serialize($cache_data), ['expire' => $expire]);
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
            return unserialize($cache);
        }
        return $default;
    }

    /**
     * 根据路由
     * @param $route
     *
     * @return mixed|null
     */
    function getRuleByRoute($route)
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
        if (!is_null($rule) && !isset($rule['expire'])) {
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
        $route = $this->makeRouteKey();
        // 读取静态规则
        $rule = $this->getRuleByRoute($route);

        if ($rule) {
            return self::createReturn(true, $rule);
        }
        return self::createReturn(false, null);;
    }

    /**
     * 返回内容
     *
     * @param $content string 数据
     * @param  string  $type  ajax 返回类型
     */
    function responseContent($content, $type = 'HTML')
    {
        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type: text/json;charset=UTF-8');
                break;
            case 'XML':
                // 返回xml格式数据
                header('Content-Type: text/xml;charset=UTF-8');
                break;
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type: application/json;charset=UTF-8');
                break;
            default:
                // 默认 HTML
                header('Content-Type: text/html;charset=UTF-8');
                header('Cache-control: '.C('HTTP_CACHE_CONTROL'));  // 页面缓存控制
        }
        // 输出模板文件
        echo $content;
        exit;
    }
}