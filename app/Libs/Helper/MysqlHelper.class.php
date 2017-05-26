<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Libs\Helper;

use Think\Model;

/**
 * Mysql工具类
 */
class MysqlHelper {

    /**
     * 获取Mysql版本
     *
     * @return string
     */
    static function getVersion() {
        $db = new Model();
        $db_version = $db->query('select version()');
        $db_version = $db_version && count($db_version) > 0 ? $db_version[0]['version()'] : '';

        return $db_version;
    }
}