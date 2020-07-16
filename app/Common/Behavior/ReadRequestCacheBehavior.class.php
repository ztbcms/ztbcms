<?php
/**
 * User: jayinton
 * Date: 2020/7/16
 * Time: 下午3:33
 */

namespace Common\Behavior;


use Common\Service\RequestCacheService;

class ReadRequestCacheBehavior extends BaseBehavior
{
    public function run(&$param)
    {
        parent::run($param);

        // 是否启动请求设置
        if (C('REQUEST_CACHE_ON')) {
            $service = new RequestCacheService();
            $result = $service->enableRequestCache();
            if ($result['status']) {
                $cache_data = $service->getCacheData();
                // 存在缓存
                if ($cache_data && isset($cache_data['data'])) {
                    $service->ajaxReturn($cache_data['data'], $cache_data['type']);
                }
            }
        }
    }

}