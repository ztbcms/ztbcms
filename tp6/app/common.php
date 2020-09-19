<?php
// 应用公共文件

/**
 * 因为挂载cms下，默认、tp6有一个 /home 开头
 * @param $string
 * @param array $vars
 * @param bool $suffix
 * @param bool $domain
 * @return \think\route\Url
 * @deprecated
 */
function urlx($string, array $vars = [], $suffix = true, $domain = false)
{
    return url('/home/' . $string, $vars, $suffix, $domain);
}

if (!function_exists('build_url')) {
    /**
     * 构建路由
     * 因为挂载cms下，默认、tp6有一个 /home 开头
     * TODO
     * @param  string $string
     * @param  array  $vars
     * @param  bool  $suffix
     * @param  bool  $domain
     *
     * @return string
     */
    function build_url($string, array $vars = [], $suffix = true, $domain = false){
        return url('/home' . $string, $vars, $suffix, $domain)->build();
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
