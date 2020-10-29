<?php
/**
 * User: jayinton
 */

namespace app\admin\libs\helper;


use think\facade\Config;

class SqlHelper
{
    /**
     * 处理sql语句，执行替换前缀都功能
     *
     * @param  string  $sql  原始的sql
     * @param  string  $table_prefix
     *
     * @return array
     */
    static function splitSQL($sql, $table_prefix = 'cms_')
    {
        // 处理前缀
        if ($table_prefix !== 'cms_') {
            $tablepre = $table_prefix;
            $sql = str_replace("cms_", $tablepre, $sql);
        }
        // 多行sql聚合为单行
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-') {
                    $ret[$num] .= $query;
                }
            }
            $num++;
        }
        return $ret;
    }
}