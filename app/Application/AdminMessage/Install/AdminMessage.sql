DROP TABLE IF EXISTS `cms_admin_message`;
CREATE TABLE `cms_admin_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '消息来源',
  `target_type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息源类型',
  `sender` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者',
  `sender_type` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者类型',
  `receiver` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者',
  `receiver_type` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者类型',
  `read_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读状态: 0未阅读 1已阅读',
  `process_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理状态：0未处理 1已处理, 2处理中',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息类型',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `read_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已阅读的人数',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;