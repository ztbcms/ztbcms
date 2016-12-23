DROP TABLE IF EXISTS `cms_sms_log`;

CREATE TABLE `cms_sms_log` (
  `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
	`operator` VARCHAR (80) NOT NULL COMMENT '运营商',
  `template` varchar(80) NOT NULL COMMENT '短信模板ID',
  `recv` text NOT NULL COMMENT '接收人',
  `param` varchar(255) DEFAULT '' COMMENT '短信模板变量',
  `sendtime` VARCHAR (80) COMMENT '发送时间',
	`result` text DEFAULT '' COMMENT '发送结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cms_sms_operator`;

CREATE TABLE `cms_sms_operator` (
  `id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
	`name` VARCHAR(80) NOT NULL COMMENT '运营商名称',
  `tablename` varchar(80) NOT NULL COMMENT '表名',
  `remark` text DEFAULT '' COMMENT '描述',
	`enable` TINYINT(4) DEFAULT 0 COMMENT '是否启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `cms_sms_operator` (`id`, `name`, `tablename`, `remark`, `enable`) VALUES ('1', '阿里大于', 'alidayu', '阿里大于短信平台', '1');
INSERT INTO `cms_sms_operator` (`id`, `name`, `tablename`, `remark`, `enable`) VALUES ('2', '云之讯', 'ucpaas', '云之讯短信平台', '0');

DROP TABLE IF EXISTS `cms_sms_alidayu`;

CREATE TABLE `cms_sms_alidayu` (
	`id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
	`type` VARCHAR (80) DEFAULT 'normal' COMMENT '短信类型',
	`extend` VARCHAR (80) DEFAULT '' COMMENT '下级会员ID',
	`sign` VARCHAR (80) COMMENT '短信签名',
	`template` VARCHAR (80) COMMENT '短信模板ID',
	`appkey` VARCHAR (255) COMMENT '应用key',
	`secret` VARCHAR (255) COMMENT '应用secret',
	PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `cms_sms_ucpaas`;

CREATE TABLE `cms_sms_ucpaas` (
	`id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
	`accountsid` VARCHAR (80) DEFAULT 'normal' COMMENT '开发者账号ID。由32个英文字母和阿拉伯数字组成的开发者账号唯一标识符。',
	`token` VARCHAR (80) DEFAULT '' COMMENT '开发者账号TOKEN',
	`appid` VARCHAR (80) COMMENT '应用ID',
	`templateid` VARCHAR (80) COMMENT '短信模板ID',
	PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;
