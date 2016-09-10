DROP TABLE IF EXISTS `cms_wechat`;
CREATE TABLE `cms_wechat` (
  `userid` int(11) unsigned NOT NULL,
  `subscribe` tinyint(1) NOT NULL COMMENT '是否关注',
  `sex` int(11) DEFAULT NULL COMMENT '性别',
  `openid` varchar(64) NOT NULL DEFAULT '' COMMENT 'openid',
  `city` varchar(128) DEFAULT '' COMMENT '所属城市',
  `province` varchar(128) DEFAULT NULL COMMENT '所属城市',
  `country` varchar(128) DEFAULT NULL COMMENT '所属国家',
  `headimgurl` varchar(512) DEFAULT NULL COMMENT '微信头像',
  `nickname` varchar(32) DEFAULT NULL COMMENT '微信昵称',
  `language` varchar(32) DEFAULT NULL COMMENT '所用语言',
  `subscribe_time` int(11) DEFAULT NULL COMMENT '关注事件',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `privilege` varchar(255) DEFAULT NULL COMMENT '特权信息',
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;