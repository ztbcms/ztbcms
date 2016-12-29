SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cms_domains
-- ----------------------------
DROP TABLE IF EXISTS `cms_domains`;
CREATE TABLE `cms_domains` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(20) DEFAULT NULL COMMENT '模块',
  `domain` varchar(255) DEFAULT NULL COMMENT '域名',
  `status` int(1) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='域名绑定';
