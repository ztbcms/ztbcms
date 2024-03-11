<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

// 注册应用的命令行
$commands = [];

// Tips: 在对应App目录中创建`console.php`, 往 $apps 填写App名称即可
$apps = ['common'];
$app_cmds = [];
foreach ($apps as $app) {
    $console_file = app_path($app . '/config/') . 'console.php';
    if (file_exists($console_file)) {
        $app_cmds = array_merge($app_cmds, require($console_file));
    }
}

return [
    // 指令定义
    'commands' => array_merge($commands, $app_cmds),
];
