<?php
// 应用公共文件

if (!function_exists('build_url')) {
    /**
     * 构建路由
     *
     * @param  string  $string
     * @param  array  $vars
     * @param  bool  $suffix
     * @param  bool  $domain
     *
     * @return string
     */
    function build_url($string, array $vars = [], $suffix = false, $domain = true)
    {
        if (strpos('/', $string) === 0) {
            return url($string, $vars, $suffix, $domain)->build();
        }
        return url('/'.$string, $vars, $suffix, $domain)->build();
    }
}

if (!function_exists('api_url')) {
    /**
     * 快捷生成API路由
     *
     * @param  string  $string  路由如：/a/b/c
     * @param  array  $vars
     *
     * @return string
     */
    function api_url($string, array $vars = [])
    {
        return build_url($string, $vars, false, true);
    }
}

if (!function_exists('createReturn')) {

    /**
     * 统一返回的格式
     *
     * @param $status
     * @param  array  $data  返回的内容
     * @param  string  $msg  提示的文案
     * @param  null  $code  状态值
     * @param  string  $url
     *
     * @return array
     */
    function createReturn($status, $data = [], $msg = '', $code = null, $url = '')
    {
        //默认成功则为200 错误则为400
        if (empty($code)) {
            $code = $status ? 200 : 400;
        }
        return [
            'status' => $status,
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
            'url'    => $url,
        ];
    }
}

if (!function_exists('cacheKey')) {
    /**
     * 生成cache key
     * @param  object  $obj 调用对象
     * @param  string  $method 调用方法
     * @param  array  $params 调用参数
     *
     * @return false|string
     */
    function cacheKey(object $obj, $method = '', $params = [])
    {
        $arr = [];
        $arr [] = get_class($obj);
        $arr [] = $method;
        foreach ($params as $key => $val) {
            $arr [] = $key.'='.$val;
        }
        return hash('md5', join(',', $arr));
    }
}

if (!function_exists('distanceBetween')) {
    /**
     * 计算两个地球坐标之间的距离，单位：千米km
     * @param $lng1 float 经度1
     * @param $lat1 float 纬度1
     * @param $lng2 float 经度2
     * @param $lat2 float 纬度2
     *
     * @return float
     */
    function distanceBetween($lng1, $lat1, $lng2, $lat2)
    {
        // 角度转换为弧度
        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        // 地球半径 6378.137km
        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
        return round($distance, 1);
    }
}


if (!function_exists('generateToken')) {
    /**
     * 生成token
     * @param string $salt 随机参数，用于分布式项目并发生成token
     *
     * @return false|string
     */
    function generateToken($salt = '')
    {
        return hash('sha256', $salt . microtime(true));
    }
}

if (!function_exists('generateUniqueId')) {
    /**
     * 生成唯一ID
     * 测试：50个进程随机生成100w个ID 不重复
     *
     * @return false|string
     */
    function generateUniqueId()
    {
        return md5(uniqid(getmypid(), true));
    }
}

if (!function_exists('now_ms')) {
    /**
     * 获取当前毫秒时间（Millisecond） 1s=1000ms
     * @return int
     */
    function now_ms()
    {
        list($usec, $sec) = explode(' ', microtime());
        return intval(($sec + $usec) * 1000);
    }
}

if (!function_exists('now_us')) {
    /**
     * 获取当前微妙(Microsecond) 1s=1000000us
     * @return int
     */
    function now_us()
    {
        list($usec, $sec) = explode(' ', microtime());
        return intval(($sec + $usec) * 1000000);
    }
}