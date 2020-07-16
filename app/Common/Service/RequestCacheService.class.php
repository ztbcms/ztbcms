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
    /**
     * 生成路由的缓存key
     *
     * @param $module
     * @param $controller
     * @param $action
     * @param  array  $param_data
     *
     * @return string
     */
    static function generateRouterCacheKey($module, $controller, $action, $param_data = [])
    {
        //key=action+md5(params)
        $route = strtolower($module.'/'.$controller.'/'.$action);
        $params = '';
        foreach ($param_data as $k => $v) {
            $params .= strtolower($k.'='.$v);
        }
        return $route.':'.md5($params);
    }

    /**
     * 生成缓存key
     *
     * @return string
     */
    private function makeCacheKey()
    {
        $param_data = I('');
        return self::generateRouterCacheKey(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME, $param_data);
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
            'key'    => $this->makeCacheKey(),
            'type'   => $ajax_return_type,
            'expire' => NOW_TIME + intval($expire),
            'data'   => $data
        ];
        return cache($cache_data['key'], json_encode($cache_data), ['expire' => $expire]);
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
        $cache = cache($this->makeCacheKey());
        if ($cache) {
            return json_decode($cache, true);
        }
        return $default;
    }

    function getRuleByRouter($route)
    {
        // 读取静态规则
        $rules = C('REQUEST_CACHE_RULES');
        $rule = null;
        if (!empty($rules)) {
            if (isset($rules[$route])) {
                $rule = $rules[$route];
            } else {
                if (isset($rules['*'])) {
                    $rule = $rules['*'];
                } else {
                    foreach ($rules as $rule_name => $v) {
                        if (strtolower($rule_name) == $route) {
                            $rule = $v;
                            break;
                        }
                    }
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