<?php

/**
 * 采集扩展函数
 */

/**
 * 把附件地址转换为系统可识别的数据格式
 * @param type $downfiles
 * @return type
 */
function changeDownfiles($downfiles) {
    $field = $GLOBALS['field'];
    $_POST[$field . '_filename'] = $_POST[$field . '_fileurl'] = array();
    $_POST[$field . '_filename'][] = basename($downfiles);
    $_POST[$field . '_fileurl'][] = $downfiles;
    return $downfiles;
}

/**
 * 连接多个字符串
 * @return string
 */
function concat() {
    $args = func_get_args();
    return implode("", $args);
}

/**
 * 地址补全
 * @param type $url 地址
 * @return string
 */
function urlSupplement($url) {
    $config = $GLOBALS['Collection_config'];
    $baseurl = $config['urlpage']; //采集地址
    $urlinfo = parse_url($baseurl);
    $baseurl = $urlinfo['scheme'] . '://' . $urlinfo['host'] . (substr($urlinfo['path'], -1, 1) === '/' ? substr($urlinfo['path'], 0, -1) : str_replace('\\', '/', dirname($urlinfo['path']))) . '/';
    if (strpos($url, '://') === false) {
        if ($url[0] == '/') {
            $url = $urlinfo['scheme'] . '://' . $urlinfo['host'] . $url;
        } else {
            if ($config['page_base']) {
                $url = $config['page_base'] . $url;
            } else {
                $url = $baseurl . $url;
            }
        }
    }
    return $url;
}
