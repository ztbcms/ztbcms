<?php
/**
 * Created by FHYI.
 * Date 2020/10/28
 * Time 10:38
 */

namespace app\common\libs\helper;

use app\common\service\BaseService;

/**
 * Mysql工具类
 * Class MysqlHelper
 */
class MysqlHelper extends BaseService
{
    /**
     * 获取Mysql版本
     *
     * @return string
     */
    static function getVersion() {
        $db_version = \think\facade\Db::query('select version()');
        $db_version = $db_version && count($db_version) > 0 ? $db_version[0]['version()'] : '';
        return $db_version;
    }
}
