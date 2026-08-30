<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 自动多应用模式声明：
    // 项目按 app/{应用名} 结构解析应用，例如访问 /news/content/index 时，
    // 会优先把 news 识别为应用，并进入 app/news/ 目录加载控制器、配置与路由
    'auto_multi_app'   => true,
    // 应用快速访问：
    // 当 URL 首段不是有效应用名时，自动回退到 default_app 指定的默认应用
    // 例如访问 /hello 时，如果不存在 app/hello/，则会回退到 home 应用
    // 此时应到 app/home/route/app.php 中定义路由，例如：Route::get('hello/[:name]', 'Index/hello');
    'app_express'    =>    true,
    // 默认应用
    'default_app'      => 'home',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getAppPath() . 'common/view/tpl_exception.php',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => env('app_debug'),
];
