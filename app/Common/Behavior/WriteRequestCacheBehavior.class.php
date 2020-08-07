<?php
/**
 * User: jayinton
 * Date: 2020/7/16
 * Time: 下午3:33
 */

namespace Common\Behavior;


use Common\Service\RequestCacheService;
use Think\Log;

/**
 * 写入请求缓存
 * 执行行为：1）view_filter 渲染完 2）ajax_return
 *
 * @package Common\Behavior
 */
class WriteRequestCacheBehavior extends BaseBehavior
{
    public function run(&$content)
    {
        parent::run($content);

        // 请求缓存
        if (C('REQUEST_CACHE_ON')) {
            $service = new RequestCacheService();
            $result = $service->enableRequestCache();
            if ($result['status']) {
                defined('REQUEST_RETURN_TYPE') or define('REQUEST_RETURN_TYPE', 'HTML');
                $service->setCacheData($content, $result['data']['expire'], REQUEST_RETURN_TYPE);
            }
        }
    }

}