<?php

namespace app\admin\controller;

use think\facade\App;
use think\facade\View;

use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use app\common\libs\helper\FileHelper;

/**
 * 后台缓存管理
 *
 * @package app\admin\controller
 */
class Cache extends AdminController
{
    /**
     * 显示缓存管理页面或执行缓存清理
     *
     * @return mixed
     */
    public function cache()
    {
        set_time_limit(0);
        $type = input('type', '', 'trim');
        if ($type === 'site') {
            // 清除后台配置缓存
            AdminConfigService::getInstance()->clearConfigCache();
            // 清除公共文件缓存
            $this->clearDirectory($this->getRuntimeRootPath() . 'cache');
            return json(self::createReturn(true, '', '清除成功'));
        } elseif ($type === 'template') {
            // 清除所有应用的模板编译缓存
            $this->clearApplicationRuntimeDirectories('temp');
            return json(self::createReturn(true, '', '清除成功'));
        } elseif ($type === 'logs') {
            // 清除公共日志及所有应用日志
            $this->clearDirectory($this->getRuntimeRootPath() . 'log');
            $this->clearApplicationRuntimeDirectories('log');
            return json(self::createReturn(true, '', '清除成功'));
        }

        return View::fetch('cache');
    }

    /**
     * 获取公共运行时目录
     *
     * @return string
     */
    private function getRuntimeRootPath(): string
    {
        return App::getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;
    }

    /**
     * 清除所有应用下指定名称的运行时目录
     *
     * @param string $directoryName 目录名称
     * @return void
     */
    private function clearApplicationRuntimeDirectories(string $directoryName): void
    {
        $paths = glob(
            $this->getRuntimeRootPath() . '*' . DIRECTORY_SEPARATOR . $directoryName,
            GLOB_ONLYDIR
        ) ?: [];

        foreach ($paths as $path) {
            $this->clearDirectory($path);
        }
    }

    /**
     * 清除指定目录内的所有文件和子目录
     *
     * @param string $path 目录路径
     * @return void
     */
    private function clearDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        (new FileHelper())->deldir($path);
    }
}
