<?php

use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

if (!function_exists('configureIsolatedTestFileCache')) {
    /**
     * 配置隔离的测试文件缓存 Store，并同步更新 Db 缓存实例
     *
     * ThinkPHP 6 在 $app->initialize() 时会提前实例化默认 'file' 缓存驱动并注入 Db::$cache。
     * 测试中通过切换到独立 store 名称绕开已缓存的 'file' 驱动实例，并同步刷新 Db::$cache，
     * 防止测试读写污染真实的 tp6/runtime/cache/ 目录。
     *
     * @param string $cachePath 临时缓存目录路径
     * @param array  $options   额外覆盖的 store 配置项（如 prefix、data_compress 等）
     * @param string $storeName 独立的缓存 store 名称，默认 'test_file'
     * @return string 实际生效的 store 名称
     */
    function configureIsolatedTestFileCache(
        string $cachePath,
        array $options = [],
        string $storeName = 'test_file'
    ): string {
        $normalizedPath = rtrim($cachePath, '/\\') . DIRECTORY_SEPARATOR;
        $storeConfig = array_merge([
            'type'       => 'File',
            'path'       => $normalizedPath,
            'prefix'     => '',
            'expire'     => 0,
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ], $options);

        Config::set([
            'default' => $storeName,
            'stores'  => [
                $storeName => $storeConfig,
            ],
        ], 'cache');

        Db::setCache(Cache::store($storeName));

        return $storeName;
    }
}
