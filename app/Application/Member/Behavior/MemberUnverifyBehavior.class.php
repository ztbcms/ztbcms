<?php

/**
 * User: zhlhuang
 * Date: 26/09/2017
 * Time: 12:20
 */

namespace Member\Behavior;

use Common\Behavior\BaseBehavior;
use Member\BehaviorParam\MemberBehaviorParam;

/**
 * 用户审核不通过触发行为
 */
class MemberUnverifyBehavior extends BaseBehavior {

    /**
     * @param MemberBehaviorParam $params
     */
    public function run(&$params) {
        parent::run($param);
    }
}
