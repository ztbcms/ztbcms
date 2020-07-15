DROP TABLE IF EXISTS `cms_admin_message`;
CREATE TABLE `cms_admin_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '消息来源',
  `target_type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息源类型',
  `receiver` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者',
  `receiver_type` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者类型',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息类型',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `read_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读状态: 0未阅读 1已阅读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;