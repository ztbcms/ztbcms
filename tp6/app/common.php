<?php
// 应用公共文件

/**
 * 因为挂载cms下，默认、tp6有一个 /home 开头
 * @param $string
 * @param array $vars
 * @param bool $suffix
 * @param bool $domain
 * @return \think\route\Url
 */
function urlx($string, array $vars = [], $suffix = true, $domain = false)
{
    return url('/home/' . $string, $vars, $suffix, $domain);
}