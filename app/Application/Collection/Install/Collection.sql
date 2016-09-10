SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `cms_collection_content`
-- ----------------------------
DROP TABLE IF EXISTS `cms_collection_content`;
CREATE TABLE `cms_collection_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nodeid` int(10) unsigned NOT NULL default '0' COMMENT '采集节点ID',
  `status` tinyint(1) unsigned NOT NULL default '0' COMMENT '采集状态{0:未采集,1:已采集,2:已导入}',
  `url` char(255) NOT NULL COMMENT '文章URL',
  `title` char(100) NOT NULL COMMENT '文章标题',
  `data` text NOT NULL COMMENT '文章数据',
  PRIMARY KEY  (`id`),
  KEY `nodeid` (`nodeid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='采集内容表';


-- ----------------------------
-- Table structure for `cms_collection_history`
-- ----------------------------
DROP TABLE IF EXISTS `cms_collection_history`;
CREATE TABLE `cms_collection_history` (
  `md5` char(32) NOT NULL COMMENT 'URL地址MD5值',
  `nodeid` smallint(6) NOT NULL COMMENT '采集节点ID',
  PRIMARY KEY  (`md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8  COMMENT='采集历史';


-- ----------------------------
-- Table structure for `cms_collection_node`
-- ----------------------------
DROP TABLE IF EXISTS `cms_collection_node`;
CREATE TABLE IF NOT EXISTS `cms_collection_node` (
  `nodeid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '采集节点ID',
  `name` varchar(20) NOT NULL COMMENT '名称',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后采集时间',
  `sourcecharset` varchar(8) NOT NULL COMMENT '采集点字符集',
  `sourcetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '网址类型',
  `urlpage` text NOT NULL COMMENT '采集地址',
  `pagesize_start` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '页码开始',
  `pagesize_end` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '页码结束',
  `page_base` char(255) NOT NULL COMMENT '网址base',
  `par_num` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '每次增加数',
  `url_contain` char(100) NOT NULL COMMENT '网址中必须包含',
  `url_except` char(100) NOT NULL COMMENT '网址中不能包含',
  `url_start` char(100) NOT NULL DEFAULT '' COMMENT '网址开始',
  `url_end` char(100) NOT NULL DEFAULT '' COMMENT '网址结束',
  `url_regular` char(100) NOT NULL DEFAULT '' COMMENT 'URL地址匹配规则',
  `title_rule` char(100) NOT NULL COMMENT '标题采集规则',
  `title_html_rule` text NOT NULL COMMENT '标题过滤规则',
  `author_rule` char(100) NOT NULL COMMENT '作者采集规则',
  `author_html_rule` text NOT NULL COMMENT '作者过滤规则',
  `comeform_rule` char(100) NOT NULL COMMENT '来源采集规则',
  `comeform_html_rule` text NOT NULL COMMENT '来源过滤规则',
  `time_rule` char(100) NOT NULL COMMENT '时间采集规则',
  `time_html_rule` text NOT NULL COMMENT '时间过滤规则',
  `content_rule` char(100) NOT NULL COMMENT '内容采集规则',
  `content_html_rule` text NOT NULL COMMENT '内容过滤规则',
  `content_page_start` char(100) NOT NULL COMMENT '内容分页开始',
  `content_page_end` char(100) NOT NULL COMMENT '内容分页结束',
  `content_page_rule` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分页模式',
  `content_page` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '内容采集是否分页',
  `content_nextpage` char(100) NOT NULL COMMENT '下一页标识符',
  `down_attachment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否下载图片',
  `watermark` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图片加水印',
  `coll_order` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '导入顺序',
  `customize_config` text NOT NULL COMMENT '自定义采集规则',
  PRIMARY KEY (`nodeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='采集节点配置' AUTO_INCREMENT=1 ;


-- ----------------------------
-- Table structure for `cms_collection_program`
-- ----------------------------
DROP TABLE IF EXISTS `cms_collection_program`;
CREATE TABLE `cms_collection_program` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL COMMENT '方案名称',
  `nodeid` int(10) unsigned NOT NULL default '0' COMMENT '采集点',
  `modelid` mediumint(6) unsigned NOT NULL default '0' COMMENT '模型ID',
  `catid` int(10) unsigned NOT NULL default '0' COMMENT '栏目ID',
  `config` text NOT NULL COMMENT '配置信息',
  PRIMARY KEY  (`id`),
  KEY `nodeid` (`nodeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='采集导入规则表';
