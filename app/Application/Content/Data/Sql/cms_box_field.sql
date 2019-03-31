CREATE TABLE `cms_box_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modelid` int(11) DEFAULT NULL COMMENT '模型id',
  `fieldid` int(11) DEFAULT NULL COMMENT '字段id',
  `label` varchar(255) DEFAULT NULL COMMENT '显示名称',
  `value` varchar(255) DEFAULT NULL COMMENT '显示值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;