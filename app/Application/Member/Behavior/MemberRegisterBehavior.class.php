<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Member\Behavior;

use Common\Behavior\BaseBehavior;
use Member\BehaviorParam\MemberBehaviorParam;

/**
 * 用户完成注册回调
 */
class MemberRegisterBehavior extends BaseBehavior {

    /**
     * @param MemberBehaviorParam $param
     */
    public function run(&$param) {
        parent::run($param);
    }

}