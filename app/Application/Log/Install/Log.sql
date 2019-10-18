
# Dump of table cms_log_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cms_log_log`;

CREATE TABLE `cms_log_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category` varchar(128) NOT NULL COMMENT '日志类别',
  `message` varchar(4098) NOT NULL COMMENT '日志内容',
  `input_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `inputtime` (`input_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;