SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cms_access
-- ----------------------------
DROP TABLE IF EXISTS `cms_access`;
CREATE TABLE `cms_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `app` varchar(255) NOT NULL DEFAULT '' COMMENT '模块',
  `controller` varchar(255) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(255) NOT NULL DEFAULT '' COMMENT '方法',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否有效 0无效 1有效',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限表';

-- ----------------------------
-- Records of cms_access
-- ----------------------------

-- ----------------------------
-- Table structure for cms_admin_panel
-- ----------------------------
DROP TABLE IF EXISTS `cms_admin_panel`;
CREATE TABLE `cms_admin_panel` (
  `mid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '菜单ID',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` char(32) NOT NULL DEFAULT '' COMMENT '菜单名',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '菜单地址',
  UNIQUE KEY `userid` (`mid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='常用菜单';

-- ----------------------------
-- Table structure for cms_config
-- ----------------------------
DROP TABLE IF EXISTS `cms_config`;
CREATE TABLE `cms_config` (
  `id` smallint(8) unsigned NOT NULL AUTO_INCREMENT,
  `varname` varchar(128) NOT NULL DEFAULT '',
  `info` varchar(255) NOT NULL DEFAULT '',
  `groupid` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `value` text,
  PRIMARY KEY (`id`),
  KEY `varname` (`varname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='网站配置表';

-- ----------------------------
-- Records of cms_config
-- ----------------------------
INSERT INTO `cms_config` VALUES ('1', 'sitename', '网站名称', '1', 'cms内容管理系统');
INSERT INTO `cms_config` VALUES ('2', 'siteurl', '网站网址', '1', '/');
INSERT INTO `cms_config` VALUES ('3', 'sitefileurl', '附件地址【废弃】', '1', '/d/file/');
INSERT INTO `cms_config` VALUES ('4', 'siteemail', '站点邮箱', '1', 'ad@qq.com');
INSERT INTO `cms_config` VALUES ('6', 'siteinfo', '网站介绍', '1', 'cms网站管理系统,是一款完全开源免费的PHP+MYSQL系统.核心采用了Thinkphp框架等众多开源软件,同时核心功能也作为开源软件发布');
INSERT INTO `cms_config` VALUES ('7', 'sitekeywords', '网站关键字', '1', 'cms内容管理系统');
INSERT INTO `cms_config` VALUES ('8', 'uploadmaxsize', '允许上传附件大小', '1', '51200');
INSERT INTO `cms_config` VALUES ('9', 'uploadallowext', '允许上传附件类型', '1', 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|mp4');
INSERT INTO `cms_config` VALUES ('10', 'qtuploadmaxsize', '前台允许上传附件大小', '1', '30720');
INSERT INTO `cms_config` VALUES ('11', 'qtuploadallowext', '前台允许上传附件类型', '1', 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|mp4');
INSERT INTO `cms_config` VALUES ('12', 'watermarkenable', '是否开启图片水印', '1', '0');
INSERT INTO `cms_config` VALUES ('13', 'watermarkminwidth', '水印-宽', '1', '227');
INSERT INTO `cms_config` VALUES ('14', 'watermarkminheight', '水印-高', '1', '78');
INSERT INTO `cms_config` VALUES ('15', 'watermarkimg', '水印图片', '1', '/statics/images/mark_bai.png');
INSERT INTO `cms_config` VALUES ('16', 'watermarkpct', '水印透明度', '1', '100');
INSERT INTO `cms_config` VALUES ('17', 'watermarkquality', '水印质量', '1', '85');
INSERT INTO `cms_config` VALUES ('18', 'watermarkpos', '水印位置', '1', '7');
INSERT INTO `cms_config` VALUES ('19', 'theme', '主题风格', '1', 'Default');
INSERT INTO `cms_config` VALUES ('20', 'ftpstatus', 'FTP上传', '1', '0');
INSERT INTO `cms_config` VALUES ('21', 'ftpuser', 'FTP用户名', '1', '');
INSERT INTO `cms_config` VALUES ('22', 'ftppassword', 'FTP密码', '1', '');
INSERT INTO `cms_config` VALUES ('23', 'ftphost', 'FTP服务器地址', '1', '');
INSERT INTO `cms_config` VALUES ('24', 'ftpport', 'FTP服务器端口', '1', '21');
INSERT INTO `cms_config` VALUES ('25', 'ftppasv', 'FTP是否开启被动模式', '1', '1');
INSERT INTO `cms_config` VALUES ('26', 'ftpssl', 'FTP是否使用SSL连接', '1', '0');
INSERT INTO `cms_config` VALUES ('27', 'ftptimeout', 'FTP超时时间', '1', '10');
INSERT INTO `cms_config` VALUES ('28', 'ftpuppat', 'FTP上传目录', '1', '/');
INSERT INTO `cms_config` VALUES ('29', 'mail_type', '邮件发送模式', '1', '1');
INSERT INTO `cms_config` VALUES ('30', 'mail_server', '邮件服务器', '1', 'smtp.qq.com');
INSERT INTO `cms_config` VALUES ('31', 'mail_port', '邮件发送端口', '1', '25');
INSERT INTO `cms_config` VALUES ('32', 'mail_from', '发件人地址', '1', 'admin@ztbcms.com');
INSERT INTO `cms_config` VALUES ('33', 'mail_auth', '密码验证', '1', '1');
INSERT INTO `cms_config` VALUES ('34', 'mail_user', '邮箱用户名', '1', '');
INSERT INTO `cms_config` VALUES ('35', 'mail_password', '邮箱密码', '1', '');
INSERT INTO `cms_config` VALUES ('36', 'mail_fname', '发件人名称', '1', 'cms管理员');
INSERT INTO `cms_config` VALUES ('37', 'domainaccess', '指定域名访问', '1', '0');
INSERT INTO `cms_config` VALUES ('38', 'generate', '是否生成首页', '1', '1');
INSERT INTO `cms_config` VALUES ('39', 'index_urlruleid', '首页URL规则', '1', '11');
INSERT INTO `cms_config` VALUES ('40', 'indextp', '首页模板', '1', 'index.php');
INSERT INTO `cms_config` VALUES ('41', 'tagurl', 'TagURL规则', '1', '8');
INSERT INTO `cms_config` VALUES ('42', 'checkcode_type', '验证码类型', '1', '1');
INSERT INTO `cms_config` VALUES ('43', 'attachment_driver', '附件驱动', '1', 'Local');
INSERT INTO `cms_config` VALUES ('44', 'attachment_aliyun_key_id', 'OSS-accessKeyId', '1', '');
INSERT INTO `cms_config` VALUES ('45', 'attachment_aliyun_key_secret', 'OSS-accessKeySecret', '1', '');
INSERT INTO `cms_config` VALUES ('46', 'attachment_aliyun_endpoint', 'OSS-Endpoint', '1', '');
INSERT INTO `cms_config` VALUES ('47', 'attachment_aliyun_bucket', 'OSS-bucket', '1', '');
INSERT INTO `cms_config` VALUES ('48', 'attachment_aliyun_domain', 'OSS-外网域名', '1', '');
INSERT INTO `cms_config` VALUES ('49', 'attachment_aliyun_privilege', 'OSS-读写权限', '1', '1');
INSERT INTO `cms_config` VALUES ('50', 'attachment_aliyun_expire_time', 'OSS-临时访问链接过期时间', '1', '86400');
INSERT INTO `cms_config` VALUES ('51', 'attachment_local_domain', '本地存储驱动-附件域名', 1, '');
INSERT INTO `cms_config` VALUES ('52', 'admin_operation_switch', '是否启用后台操作日志', 1, 1);
INSERT INTO `cms_config` VALUES ('53', 'attachment_aliyun_is_direct', 'oss-开启直传', '1', '0');
INSERT INTO `cms_config` VALUES ('54', 'downloader_retry_switch', '下载中心-任务启动失败是否重启', '1', '0');
INSERT INTO `cms_config` VALUES ('55', 'downloader_retry_num', '下载中心-重启的次数', '1', '3');
INSERT INTO `cms_config` VALUES ('56', 'downloader_timeout', '下载中心-下载超时时间（秒）', '1', '300');
INSERT INTO `cms_config` VALUES ('57', 'downloader_domain', '下载中心-访问域名', '1', '');
-- 七牛云配置
INSERT INTO `cms_config` VALUES ('58', 'attachment_qiniu_access_key', '七牛云-AccessKey', '1', '');
INSERT INTO `cms_config` VALUES ('59', 'attachment_qiniu_secret_key', '七牛云-SecretKey', '1', '');
INSERT INTO `cms_config` VALUES ('60', 'attachment_qiniu_bucket', '七牛云-Bucket', '1', '');
INSERT INTO `cms_config` VALUES ('61', 'attachment_qiniu_domain', '七牛云-CDN域名', '1', '');
INSERT INTO `cms_config` VALUES ('62', 'attachment_qiniu_privilege', '七牛云-读写权限', '1', '1');
INSERT INTO `cms_config` VALUES ('63', 'attachment_qiniu_expire_time', '七牛云-临时访问链接过期时间', '1', '3600');
INSERT INTO `cms_config` VALUES ('64', 'attachment_aliyun_sts_role_arn', 'OSS-STS角色ARN', '1', '');
INSERT INTO `cms_config` VALUES ('65', 'attachment_direct_file_dir_template', '直传文件路径模板', '1', '{module}/{Y}{m}{d}/');


-- ----------------------------
-- Table structure for cms_config_field
-- ----------------------------
DROP TABLE IF EXISTS `cms_config_field`;
CREATE TABLE `cms_config_field` (
  `fid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增长id',
  `fieldname` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '类型,input',
  `setting` mediumtext COMMENT '其他',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='网站配置，扩展字段列表';

-- ----------------------------
-- Records of cms_config_field
-- ----------------------------

-- ----------------------------
-- Table structure for cms_login_log
-- ----------------------------
DROP TABLE IF EXISTS `cms_login_log`;
CREATE TABLE `cms_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '登录帐号',
  `logintime` int(10) NOT NULL DEFAULT '0' COMMENT '登录时间戳',
  `loginip` char(20) NOT NULL DEFAULT '' COMMENT '登录IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态,1为登录成功，0为登录失败',
  `password` varchar(30) NOT NULL DEFAULT '' COMMENT '尝试错误密码',
  `info` varchar(255) NOT NULL DEFAULT '' COMMENT '其他说明',
  PRIMARY KEY (`id`),
  KEY `logintime` (`logintime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台登录日志';

-- ----------------------------
-- Table structure for cms_menu
-- ----------------------------
DROP TABLE IF EXISTS `cms_menu`;
CREATE TABLE `cms_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `parentid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `app` varchar(255) NOT NULL DEFAULT '' COMMENT '应用标识',
  `controller` varchar(255) NOT NULL DEFAULT '' COMMENT '控制键',
  `action` varchar(255) NOT NULL DEFAULT '' COMMENT '方法',
  `parameter` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0无需权限验证菜单 1需权限验证菜单',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0不展示 1展示',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  PRIMARY KEY (`id`),
  KEY `parentid` (`parentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台菜单表';


-- ----------------------------
-- Table structure for cms_module
-- ----------------------------
DROP TABLE IF EXISTS `cms_module`;
CREATE TABLE `cms_module` (
  `module` varchar(15) NOT NULL COMMENT '模块',
  `modulename` varchar(20) NOT NULL DEFAULT '' COMMENT '模块名称',
  `sign` varchar(255) NOT NULL DEFAULT '' COMMENT '签名',
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '内置模块',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可用',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本',
  `setting` mediumtext COMMENT '设置信息',
  `installtime` int(10) NOT NULL DEFAULT '0' COMMENT '安装时间',
  `updatetime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='已安装模块列表';

-- ----------------------------
-- Records of cms_module
-- ----------------------------


DROP TABLE IF EXISTS `cms_operation_log`;
CREATE TABLE `cms_operation_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `uid` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作帐号ID',
    `time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
    `ip` char(20) NOT NULL DEFAULT '' COMMENT 'IP',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态,0错误提示，1为正确提示',
    `method` varchar(32) NOT NULL DEFAULT '' COMMENT '请求方法',
    `url` varchar(512) NOT NULL DEFAULT '' COMMENT '请求路由',
    `params` text NOT NULL COMMENT '请求参数',
    `response` text NOT NULL COMMENT '响应结果',
    PRIMARY KEY (`id`),
    KEY `time` (`time`),
    KEY `uid_time` (`uid`, `time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台操作日志表';

-- ----------------------------
-- Table structure for cms_role
-- ----------------------------
DROP TABLE IF EXISTS `cms_role`;
CREATE TABLE `cms_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '角色名称',
  `parentid` smallint(6) NOT NULL DEFAULT '0' COMMENT '父角色ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '启用状态 0禁用 1启用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序字段',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色信息列表';

-- ----------------------------
-- Records of cms_role
-- ----------------------------
INSERT INTO `cms_role` VALUES ('1', '超级管理员', '0', '1', '拥有网站最高管理员权限！', '1329633709', '1329633709', '0');
INSERT INTO `cms_role` VALUES ('2', '站点管理员', '1', '1', '站点管理员', '1329633722', '1399780945', '0');
INSERT INTO `cms_role` VALUES ('3', '发布人员', '2', '1', '发布人员', '1329633733', '1399798954', '0');

-- ----------------------------
-- Table structure for cms_user
-- ----------------------------
DROP TABLE IF EXISTS `cms_user`;
CREATE TABLE `cms_user` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称/姓名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `bind_account` varchar(50) NOT NULL DEFAULT '' COMMENT '绑定帐户',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `last_login_ip` varchar(40) NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `verify` varchar(32) NOT NULL DEFAULT '' COMMENT '证验码',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `role_id` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '对应角色ID',
  `info` text NOT NULL COMMENT '信息',
  `avatar` varchar(256) NOT NULL DEFAULT '' COMMENT '头像链接',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台用户表';

-- ----------------------------
-- Table structure for cms_admin_message
-- ----------------------------
DROP TABLE IF EXISTS `cms_admin_message`;
CREATE TABLE `cms_admin_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '消息源',
  `target_type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息源类型',
  `sender` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者',
  `sender_type` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者类型',
  `receiver` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者',
  `receiver_type` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者类型',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '消息类型',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `read_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读状态: 0未阅读 1已阅读',
  PRIMARY KEY (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台消息';

DROP TABLE IF EXISTS `cms_email_send_log`;
CREATE TABLE `cms_email_send_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to_email` varchar(64) NOT NULL DEFAULT '' COMMENT '接收邮箱',
  `from_email` varchar(64) NOT NULL DEFAULT '' COMMENT '发送邮箱',
  `subject` varchar(256) NOT NULL DEFAULT '' COMMENT '发送标题',
  `content` text NOT NULL COMMENT '发送内容',
  `status` tinyint(11) NOT NULL DEFAULT '1' COMMENT '发送状态 0失败 1成功',
  `error_msg` varchar(512) NOT NULL DEFAULT '' COMMENT '错误信息',
  `send_time` int(11) NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`id`),
  KEY `send_time` (`send_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邮件发送记录';

DROP TABLE IF EXISTS `cms_user_operate_log`;
CREATE TABLE `cms_user_operate_log`
(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` smallint(6) NOT NULL COMMENT '操作帐号ID',
  `user_name` varchar(255) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `ip` char(20) NOT NULL DEFAULT '' COMMENT 'IP',
  `source_type` varchar(255) NOT NULL DEFAULT '' COMMENT '来源类型',
  `source` varchar(255) NOT NULL DEFAULT '' COMMENT '来源项',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '操作内容',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
    PRIMARY KEY (`id`),
    KEY `user_id_create_time` (`user_id`, `create_time`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COMMENT='用户操作日志';

DROP TABLE IF EXISTS `cms_kv`;
CREATE TABLE `cms_kv`
(
    `key`         varchar(255) NOT NULL,
    `value`       text         NOT NULL,
    `create_time` int(11) NOT NULL,
    `update_time` int(11) NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
