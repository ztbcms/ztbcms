<?php
// 应用公共文件

/**
 * 因为挂载cms下，默认、tp6有一个 /home 开头
 * @param $string
 * @return \think\route\Url
 */
function urlx($string)
{
    return url('/home/' . $string);
}