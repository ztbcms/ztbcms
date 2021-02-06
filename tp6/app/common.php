<?php
// 应用公共文件

if (!function_exists('build_url')) {
    /**
     * 构建路由
     * @param  string $string
     * @param  array  $vars
     * @param  bool  $suffix
     * @param  bool  $domain
     *
     * @return string
     */
    function build_url($string, array $vars = [], $suffix = false, $domain = true){
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
     * @param string $string 路由如：/a/b/c
     * @param  array  $vars
     *
     * @return string
     */
    function api_url($string, array $vars = [])
    {
        return build_url($string, $vars, false, true);
    }
}

if(!function_exists('createReturn')) {

    /**
     * 统一返回的格式
     * @param $status
     * @param array $data 返回的内容
     * @param string $msg 提示的文案
     * @param null $code 状态值
     * @param string $url
     * @return array
     */
    function createReturn($status, $data = [], $msg = '', $code = null, $url = '') {
        //默认成功则为200 错误则为400
        if(empty($code)) $code = $status ? 200 : 400;
        return [
            'status' => $status,
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
            'url'    => $url,
        ];
    }

}
