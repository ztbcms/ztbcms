<?php

// +----------------------------------------------------------------------
// |  配置
// +----------------------------------------------------------------------
return array(
    'AUTOLOAD_NAMESPACE' => array_merge(C('AUTOLOAD_NAMESPACE'), array(
        'Addon' => PROJECT_PATH . 'Addon',
    )),
);
