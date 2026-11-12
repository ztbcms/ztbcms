<?php
/**
 * User: jayinton
 */

namespace app\common\libs\helper;


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
        // 多行sql聚合为单行
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $line = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-') {
                    $line .= $query;
                }
            }
            if (!empty($line)) {
                if ($table_prefix !== 'cms_') {
                    $line = self::replaceTablePrefix($line, (string) $table_prefix);
                }
                $ret[$num] = $line;
                $num++;
            }
        }
        return $ret;
    }

    /**
     * 仅替换 SQL 语句中表名位置的默认前缀 cms_，避免误替换字段名或数据内容
     *
     * @param string $sql
     * @param string $tablePrefix
     * @return string
     */
    protected static function replaceTablePrefix(string $sql, string $tablePrefix): string
    {
        $pattern = '/^(\s*(?i:DROP\s+(?:TEMPORARY\s+)?TABLE(?:\s+IF\s+EXISTS)?|CREATE\s+(?:TEMPORARY\s+)?TABLE(?:\s+IF\s+NOT\s+EXISTS)?|INSERT(?:\s+IGNORE)?\s+INTO|REPLACE\s+INTO|ALTER\s+TABLE|UPDATE(?:\s+IGNORE)?|DELETE\s+FROM|TRUNCATE(?:\s+TABLE)?)(?:\s+`?|`))(cms_)([a-zA-Z0-9_]+`?)/';

        return (string) preg_replace_callback(
            $pattern,
            function (array $matches) use ($tablePrefix) {
                return $matches[1] . $tablePrefix . $matches[3];
            },
            $sql,
            1
        );
    }
}