DROP TABLE IF EXISTS `cms_trade`;
CREATE TABLE `cms_trade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '上一条记录id （方便追溯到上一条记录id）',
  `userid` int(11) DEFAULT NULL COMMENT '所属用户',
  `income` float NOT NULL DEFAULT '0' COMMENT '收入',
  `pay` float DEFAULT NULL COMMENT '支出',
  `balance` float DEFAULT NULL COMMENT '余额',
  `type` varchar(255) DEFAULT '' COMMENT '类型',
  `trade_no` varchar(255) DEFAULT NULL COMMENT '交易凭证和交易类型确定该交易的来源',
  `detail` varchar(255) DEFAULT NULL COMMENT '详细描述，可以是中文',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '1为有效0无效',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;