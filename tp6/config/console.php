<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

// 注册应用的命令行
$commands = [];

if (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
    return [
        // 指令定义
        'commands' => $commands,
    ];
}

$appCmds = [];
$consoleFiles = glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'console.php') ?: [];

sort($consoleFiles, SORT_STRING);

foreach ($consoleFiles as $consoleFile) {
    $appCommands = require $consoleFile;
    if (is_array($appCommands)) {
        $appCmds = array_merge($appCmds, $appCommands);
    }
}

return [
    // 指令定义
    'commands' => array_merge($commands, $appCmds),
];
