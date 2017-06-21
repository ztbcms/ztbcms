<?php

// +----------------------------------------------------------------------
// | 一些自定义的通用函数
// +----------------------------------------------------------------------

/**
 * 统一格式返回
 * @param bool $status
 * @param array $data
 * @param string $msg
 * @param string $url
 * @return array
 */
function createReturn($status = true, $data = [], $msg = '', $url = ''){
    return [
        'status' => $status,
        'data' => $data,
        'msg' => $msg,
        'url' => $url
    ];
}