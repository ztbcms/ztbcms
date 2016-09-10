SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `cms_comments`
-- ----------------------------
DROP TABLE IF EXISTS `cms_comments`;
CREATE TABLE `cms_comments` (
  `id` bigint(20) unsigned NOT NULL auto_increment COMMENT '评论ID',
  `comment_id` char(30) NOT NULL COMMENT '所属文章id',
  `author` tinytext NOT NULL COMMENT '评论者名称',
  `author_email` varchar(100) NOT NULL default '' COMMENT '评论者电邮地址',
  `author_url` varchar(200) NOT NULL default '' COMMENT '评论者网址',
  `author_ip` varchar(100) NOT NULL default '' COMMENT '评论者的IP地址',
  `date` int(11) NOT NULL COMMENT '评论发表时间',
  `approved` varchar(20) NOT NULL default '1' COMMENT '评论状态，0为审核，1为正常',
  `agent` varchar(255) NOT NULL default '' COMMENT '评论者的客户端信息',
  `parent` bigint(20) unsigned NOT NULL default '0' COMMENT '上级评论id',
  `user_id` bigint(20) unsigned NOT NULL default '0' COMMENT '评论对应用户id',
  `stb` varchar(6) NOT NULL COMMENT '存放副表名',
  PRIMARY KEY  (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `approved` (`approved`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论表';

-- ----------------------------
-- Table structure for `cms_comments_data_1`
-- ----------------------------
DROP TABLE IF EXISTS `cms_comments_data_1`;
CREATE TABLE `cms_comments_data_1` (
  `id` bigint(20) unsigned NOT NULL COMMENT '评论id',
  `comment_id` char(30) NOT NULL COMMENT '所属文章Id',
  `content` text NOT NULL COMMENT '评论内容',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论副表1';

-- ----------------------------
-- Table structure for `cms_comments_emotion`
-- ----------------------------
DROP TABLE IF EXISTS `cms_comments_emotion`;
CREATE TABLE `cms_comments_emotion` (
  `emotion_id` int(10) unsigned NOT NULL auto_increment COMMENT '表情ID',
  `emotion_name` varchar(20) NOT NULL default '' COMMENT '表情名称',
  `emotion_icon` varchar(50) NOT NULL default '' COMMENT '表情图标',
  `vieworder` int(10) unsigned NOT NULL default '0' COMMENT '排序',
  `isused` tinyint(3) unsigned NOT NULL default '1' COMMENT '是否使用',
  PRIMARY KEY  (`emotion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='表情数据表';

-- ----------------------------
-- Table structure for `cms_comments_field`
-- ----------------------------
DROP TABLE IF EXISTS `cms_comments_field`;
CREATE TABLE `cms_comments_field` (
  `fid` smallint(6) NOT NULL auto_increment COMMENT '字段id',
  `f` varchar(30) NOT NULL COMMENT '字段名',
  `fname` varchar(30) NOT NULL COMMENT '字段标识',
  `fzs` varchar(255) NOT NULL COMMENT '注释',
  `ftype` varchar(30) NOT NULL COMMENT '字段类型',
  `flen` varchar(20) NOT NULL COMMENT '字段长度',
  `ismust` tinyint(1) NOT NULL COMMENT '1为必填，0为非必填',
  `issystem` tinyint(1) NOT NULL COMMENT '是否添加到主表',
  `regular` varchar(255) NOT NULL COMMENT '数据验证正则',
  `system` tinyint(1) NOT NULL COMMENT '是否系统字段',
  PRIMARY KEY  (`fid`),
  UNIQUE KEY `fname` (`fname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论自定义字段表';

-- ----------------------------
-- Records of cms_comments_field
-- ----------------------------
INSERT INTO `cms_comments_field` VALUES ('1', 'author', '昵称', '昵称不能为空！', 'TEXT', '', '1', '1', '', '1');
INSERT INTO `cms_comments_field` VALUES ('2', 'author_email', '邮箱', '邮箱不能为空！', 'VARCHAR', '100', '1', '1', '/^[\\w\\-\\.]+@[\\w\\-\\.]+(\\.\\w+)+$/', '1');
INSERT INTO `cms_comments_field` VALUES ('3', 'author_url', '网站地址', '网站地址不能为空！', 'VARCHAR', '200', '0', '1', '/^http:\\/\\//', '1');

-- ----------------------------
-- Table structure for `cms_comments_setting`
-- ----------------------------
DROP TABLE IF EXISTS `cms_comments_setting`;
CREATE TABLE `cms_comments_setting` (
  `guest` tinyint(1) NOT NULL COMMENT '是否允许游客评论',
  `code` tinyint(1) NOT NULL COMMENT '是否开启验证码',
  `check` tinyint(1) NOT NULL COMMENT '是否需要审核',
  `stb` tinyint(4) NOT NULL COMMENT '存储分表',
  `stbsum` int(4) NOT NULL COMMENT '分表总数',
  `order` varchar(20) NOT NULL COMMENT '排序',
  `strlength` int(5) NOT NULL COMMENT '允许最大字数',
  `status` tinyint(1) NOT NULL COMMENT '关闭/开启评论',
  `expire` int(11) NOT NULL COMMENT '评论间隔时间单位秒'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论配置';

-- ----------------------------
-- Records of cms_comments_setting
-- ----------------------------
INSERT INTO `cms_comments_setting` VALUES ('1', '0', '0', '1', '1', 'date DESC', '400', '1', '60');
