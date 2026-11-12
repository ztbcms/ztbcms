<?php

use app\common\libs\helper\SqlHelper;

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

/**
 * 断言条件成立
 *
 * @param bool $condition
 * @param string $message
 * @return void
 */
function assertSqlHelper(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// 1. 验证默认前缀 cms_ 不触发替换，且能保留数据中的 cms_
$rawWithDefaultPrefix = <<<'SQL'
-- Table structure for cms_config
DROP TABLE IF EXISTS `cms_config`;
CREATE TABLE `cms_config` (
  `id` int(11) NOT NULL,
  `cms_key` varchar(64) NOT NULL DEFAULT 'cms_default'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `cms_config` VALUES ('1', 'cms_sitename');
SQL;

$defaultRes = SqlHelper::splitSQL($rawWithDefaultPrefix, 'cms_');
assertSqlHelper(count($defaultRes) === 3, '默认前缀切分语句数量应为 3');
assertSqlHelper($defaultRes[0] === 'DROP TABLE IF EXISTS `cms_config`', '默认前缀 DROP 语句不匹配');
assertSqlHelper(strpos($defaultRes[1], 'CREATE TABLE `cms_config`') === 0, '默认前缀 CREATE 语句不匹配');
assertSqlHelper($defaultRes[2] === "INSERT INTO `cms_config` VALUES ('1', 'cms_sitename');", '默认前缀 INSERT 语句不匹配');

// 2. 验证自定义前缀仅替换表名位置的 cms_，不污染数据内容、字段名与注释
$sampleSql = <<<'SQL'
-- Table structure for cms_demo
# Another comment with cms_demo
DROP TABLE IF EXISTS `cms_demo`;
CREATE TABLE IF NOT EXISTS `cms_demo` (
  `id` int(11) NOT NULL,
  `cms_field` varchar(64) DEFAULT 'cms_default' COMMENT '字段注释 cms_note',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `cms_demo` (`id`, `cms_field`) VALUES (1, '数据内容包含 cms_ 不会被替换, CREATE TABLE `cms_fake` 也不会被替换');
INSERT IGNORE INTO `cms_demo` VALUES (2, 'cms_ignore_value');
REPLACE INTO cms_demo VALUES (3, 'cms_replace_value');
UPDATE `cms_demo` SET `cms_field` = 'cms_updated' WHERE `cms_field` = 'cms_default';
DELETE FROM `cms_demo` WHERE `cms_field` = 'cms_updated';
ALTER TABLE `cms_demo` ADD COLUMN `cms_extra` varchar(32) DEFAULT 'cms_val';
TRUNCATE TABLE `cms_demo`;
SQL;

$customRes = SqlHelper::splitSQL($sampleSql, 'ztb_');
$expectedCustomRes = [
    'DROP TABLE IF EXISTS `ztb_demo`',
    "CREATE TABLE IF NOT EXISTS `ztb_demo` (  `id` int(11) NOT NULL,  `cms_field` varchar(64) DEFAULT 'cms_default' COMMENT '字段注释 cms_note',  PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "INSERT INTO `ztb_demo` (`id`, `cms_field`) VALUES (1, '数据内容包含 cms_ 不会被替换, CREATE TABLE `cms_fake` 也不会被替换')",
    "INSERT IGNORE INTO `ztb_demo` VALUES (2, 'cms_ignore_value')",
    "REPLACE INTO ztb_demo VALUES (3, 'cms_replace_value')",
    "UPDATE `ztb_demo` SET `cms_field` = 'cms_updated' WHERE `cms_field` = 'cms_default'",
    "DELETE FROM `ztb_demo` WHERE `cms_field` = 'cms_updated'",
    "ALTER TABLE `ztb_demo` ADD COLUMN `cms_extra` varchar(32) DEFAULT 'cms_val'",
    'TRUNCATE TABLE `ztb_demo`;',
];

assertSqlHelper($customRes === $expectedCustomRes, '自定义前缀替换结果与预期不一致');

// 3. 验证空表前缀（''）能够正确移除表名 cms_ 前缀而不影响数据内容
$emptyPrefixRes = SqlHelper::splitSQL("INSERT INTO `cms_config` VALUES ('1', 'cms_value');\n", '');
assertSqlHelper(
    $emptyPrefixRes === ["INSERT INTO `config` VALUES ('1', 'cms_value');"],
    '空前缀替换结果不符合预期'
);

// 4. 回归验证仓库内核心安装 SQL（cms.sql 及模块安装/卸载 SQL）所有表名前缀均被完整替换
$sqlFiles = [
    dirname(__DIR__) . '/app/install/data/cms.sql',
    dirname(__DIR__) . '/app/common/install/Common.sql',
    dirname(__DIR__) . '/app/common/uninstall/Common.sql',
];
foreach ($sqlFiles as $sqlFile) {
    assertSqlHelper(is_file($sqlFile), 'SQL 文件不存在: ' . $sqlFile);
    $statements = SqlHelper::splitSQL((string) file_get_contents($sqlFile), 'ztb_');
    assertSqlHelper(!empty($statements), 'SQL 文件切分结果不应为空: ' . $sqlFile);
    foreach ($statements as $index => $statement) {
        assertSqlHelper(
            strpos($statement, 'cms_') === false,
            sprintf('文件 %s 第 %d 条语句仍残留未替换的 cms_: %s', basename($sqlFile), $index, $statement)
        );
    }
}

echo "sql_helper_split_sql_test passed\n";
