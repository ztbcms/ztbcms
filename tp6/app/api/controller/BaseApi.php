<?php

/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\api\controller;

use app\api\middleware\ApiAuth;
use app\api\middleware\ApiRateLimit;
use app\BaseController;
use think\App;

/**
 * API基类
 */
class BaseApi extends BaseController
{

    /**
     * 跳过登录验证的Action
     * 注：
     * 1、若跳过全部，可以填写*
     * 2、跳过指定action，可填写 ['actionA', 'actionB']
     */
    protected $skillAuthActions = [];
    // 尝试认证Actions
    protected $tryAuthActions = [];
    /**
     * API 速率限制配置
     *
     * 说明：
     * 1、默认对当前控制器下的全部 API action 生效
     * 2、默认规则：同一 IP 同一接口，60 秒内最多 60 次
     * 3、`actions` 填 `*` 表示对全部 action 生效
     * 4、`except` 可排除不需要限制的 action
     * 5、`rules` 可给单个 action 单独设置更严格或更宽松的规则
     *
     * 示例：
     * protected $apiRateLimit = [
     *     'enabled' => true,
     *     'actions' => ['*'], // 全部接口启用限制
     *     'except' => ['ping'], // ping 不限制
     *     'max_requests' => 60, // 默认 60 秒最多 60 次
     *     'decay_seconds' => 60,
     *     'rules' => [
     *         'login' => [
     *             'max_requests' => 20, // login 单独收紧到 60 秒 20 次
     *             'decay_seconds' => 60,
     *         ],
     *         'sendSms' => [
     *             'max_requests' => 5, // sendSms 单独收紧到 60 秒 5 次
     *             'decay_seconds' => 60,
     *         ],
     *     ],
     * ];
     */
    protected $apiRateLimit = [
        'enabled' => false,
        'actions' => ['*'],
        'except' => [],
        'max_requests' => 60,
        'decay_seconds' => 60,
        'rules' => [],
    ];

    protected $middleware = [
        ApiRateLimit::class,
        ApiAuth::class,
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
        // 往请求注入
        $app->request->skillAuthActions = $this->skillAuthActions ?? [];
        $app->request->tryAuthActions = $this->tryAuthActions ?? [];
        $app->request->apiRateLimit = $this->apiRateLimit;
    }
}
