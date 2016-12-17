<?php
/**
 * Copyright since 2016
 * Author: Jayin Ton
 * Date: 2016/10/24 15:02
 */

namespace Common\BehaviorParam;

/**
 * 行为回调参数
 *
 * @package Common\BehaviorParam
 */
class BaseBehaviorParam {

    function __get($name) {
        return $this->$name;
    }

    function __set($name, $value) {
        $this->$name = $value;
    }


    /**
     * 创建参数实例
     * @param array $data
     * @return mixed
     */
    static function create(array $data) {
        $class = __CLASS__;
        $param = new $class;
        foreach ($data as $key => $val){
            if(empty($param->$key)){
                $param->$key = $val;
            }
        }
        return $param;
    }
}