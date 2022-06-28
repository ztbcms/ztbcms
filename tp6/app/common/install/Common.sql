-- ----------------------------
-- 计划任务
-- ----------------------------
DROP TABLE IF EXISTS `cms_tp6_cron`;
CREATE TABLE `cms_tp6_cron` (
  `cron_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '计划任务ID',
  `type` tinyint(2) DEFAULT '0' COMMENT '计划任务类型',
  `subject` varchar(50) NOT NULL DEFAULT '' COMMENT '计划任务名称',
  `loop_type` varchar(10) NOT NULL DEFAULT '' COMMENT '循环类型month/week/day/hour/now',
  `loop_daytime` varchar(50) NOT NULL DEFAULT '' COMMENT '循环类型时间（日-时-分）',
  `cron_file` varchar(512) NOT NULL DEFAULT '' COMMENT '计划任务执行文件',
  `isopen` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启 0 否，1是，2系统任务',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务创建时间',
  `modified_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划任务上次执行结束时间',
  `next_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下一次执行时间',
  `data` text COMMENT '数据',
  PRIMARY KEY (`cron_id`),
  KEY `idx_next_time` (`next_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='计划任务表';

DROP TABLE IF EXISTS `cms_tp6_cron_log`;
CREATE TABLE `cms_tp6_cron_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cron_id` int(11) NOT NULL COMMENT '计划任务ID',
  `start_time` int(11) NOT NULL COMMENT '开始时间',
  `end_time` int(11) NOT NULL COMMENT '结束时间',
  `result` tinyint(2) NOT NULL DEFAULT '1' COMMENT '执行结果：0待执行 1正常 2异常 3执行中',
  `use_time` int(11) NOT NULL DEFAULT '0' COMMENT '耗时',
  `result_msg` text COMMENT '执行日志信息',
  PRIMARY KEY (`id`),
  KEY `result` (`result`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='计划任务执行日志';

DROP TABLE IF EXISTS `cms_tp6_cron_scheduling_log`;
CREATE TABLE `cms_tp6_cron_scheduling_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start_time` int(11) NOT NULL COMMENT '开始时间',
  `end_time` int(11) NOT NULL COMMENT '结束时间',
  `use_time` int(11) NOT NULL COMMENT '耗时',
  `error_count` int(11) NOT NULL COMMENT '错误数量',
  `cron_count` int(11) NOT NULL COMMENT '周期内执行计划任务次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='调度运行日志';

-- 配置表
DROP TABLE IF EXISTS `cms_tp6_cron_config`;
CREATE TABLE `cms_tp6_cron_config` (
  `key` varchar(32) NOT NULL DEFAULT '' COMMENT '键',
  `value` varchar(256) NOT NULL DEFAULT '' COMMENT '值',
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '标题',
  `descrption` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `cms_tp6_cron_config` (`key`, `value`, `title`, `descrption`)
VALUES
	('enable_cron', '1', '是否启用', '1启动 0停止'),
	('secret_key', '', '私钥', '');

-- ----------------------------
-- 消息
-- ----------------------------

DROP TABLE IF EXISTS `cms_tp6_message_msg`;
CREATE TABLE `cms_tp6_message_msg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '消息内容',
  `target` varchar(128) NOT NULL DEFAULT '' COMMENT '消息源',
  `target_type` varchar(128) NOT NULL DEFAULT '' COMMENT '消息源类型',
  `sender` varchar(128) NOT NULL DEFAULT '' COMMENT '发送者',
  `sender_type` varchar(128) NOT NULL DEFAULT '' COMMENT '发送者类型',
  `receiver` varchar(128) NOT NULL DEFAULT '' COMMENT '接收者',
  `receiver_type` varchar(128) NOT NULL DEFAULT '' COMMENT '接收者类型',
  `read_status` int(11) NOT NULL DEFAULT '0' COMMENT '阅读状态: 0未阅读 1已阅读',
  `process_status` int(11) NOT NULL DEFAULT '0' COMMENT '处理状态：0未处理 1已处理, 2处理中',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `send_time` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `type` varchar(128) NOT NULL DEFAULT 'message' COMMENT '消息类型：message私信 remind提醒 announce公告',
  `class` varchar(128) NOT NULL DEFAULT '' COMMENT '实例化的类名',
  `read_time` int(11) NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `process_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '处理次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='消息记录';

DROP TABLE IF EXISTS `cms_tp6_message_send_log`;
CREATE TABLE `cms_tp6_message_send_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(11) DEFAULT '0' COMMENT '消息id',
  `sender` varchar(256) DEFAULT '' COMMENT '消息处理器',
  `status` tinyint(3) DEFAULT '0' COMMENT '处理状态0不成，1处理成功',
  `result_msg` varchar(1024) DEFAULT '' COMMENT '处理结果',
  `create_time` int(11) DEFAULT '0' COMMENT '日志创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='消息发送处理日志';


-- ----------------------------
-- 上传
-- ----------------------------

DROP TABLE IF EXISTS `cms_attachment_group`;
CREATE TABLE `cms_attachment_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父ID',
  `group_type` varchar(255) NOT NULL DEFAULT '' COMMENT '分类类型',
  `group_name` varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `delete_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='附件分类';

DROP TABLE IF EXISTS `cms_attachment`;
CREATE TABLE `cms_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `driver` varchar(32) DEFAULT 'Local' COMMENT '上传驱动',
  `group_id` int(11) DEFAULT '0' COMMENT '分组',
  `module` varchar(64) NOT NULL DEFAULT '' COMMENT '模块名称',
  `filename` varchar(256) NOT NULL DEFAULT '' COMMENT '上传附件名称',
  `filepath` varchar(256) NOT NULL DEFAULT '' COMMENT '附件路径',
  `fileurl` varchar(256) DEFAULT '' COMMENT '文件全局路径',
  `filethumb` varchar(256) DEFAULT '' COMMENT '文件缩略图',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件大小',
  `fileext` varchar(16) NOT NULL DEFAULT '' COMMENT '附件扩展名',
  `is_private` tinyint(1) DEFAULT '0' COMMENT '是否私有链接',
  `upload_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '上传ip',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
  `user_type` varchar(32) DEFAULT NULL COMMENT '上传用户类型 admin后台',
  `user_id` varchar(16) DEFAULT NULL COMMENT '上传用户ID',
  `hash` varchar(64) DEFAULT '' COMMENT '附件hash值（md5）',
  PRIMARY KEY (`aid`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='附件表';


DROP TABLE IF EXISTS `cms_attachment_index`;
CREATE TABLE `cms_attachment_index` (
    `keyid` varchar(128) NOT NULL DEFAULT '' COMMENT '关联id',
    `aid` int(11) NOT NULL COMMENT '附件ID',
    KEY `keyid` (`keyid`),
    KEY `aid` (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='附件关系表';

-- ----------------------------
-- 队列
-- ----------------------------
DROP TABLE IF EXISTS `cms_queue_jobs`;
CREATE TABLE `cms_queue_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserve_time` int(11) unsigned DEFAULT NULL,
  `available_time` int(11) unsigned NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='队列-任务表';

DROP TABLE IF EXISTS `cms_queue_failed_jobs`;
CREATE TABLE `cms_queue_failed_jobs` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `connection` text NOT NULL,
 `queue` text NOT NULL,
 `payload` longtext NOT NULL,
 `exception` longtext NOT NULL,
 `fail_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='队列-失败任务表';

-- ----------------------------
-- 队列 END
-- ----------------------------

-- ----------------------------
-- 下载中心
-- ----------------------------
DROP TABLE IF EXISTS `cms_downloader`;
CREATE TABLE `cms_downloader`  (
    `downloader_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
    `downloader_url` text NOT NULL COMMENT '下载链接',
    `downloader_url_hash` varchar(255) DEFAULT NULL COMMENT '下载链接Hash',
    `downloader_state` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '下载状态 （10待下载 20下载中  30下载成功 40下载失败）',
    `downloader_result` varchar(255) NOT NULL DEFAULT '' COMMENT '下载结果',
    `downloader_duration` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载时长',
    `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名称',
    `file_path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
    `file_url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件访问地址',
    `file_thumb` varchar(256) NOT NULL DEFAULT '' COMMENT '文件缩略图',
    `file_hash` varchar(255) NOT NULL DEFAULT '' COMMENT '文件md5',
    `file_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小(bytes)',
    `file_ext` varchar(32) NOT NULL DEFAULT '' COMMENT '文件扩展名',
    `downloader_implement_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下载执行次数',
    `downloader_next_implement_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下一次执行的时间',
    `create_time` int(11) unsigned DEFAULT '0' COMMENT '上传时间',
    `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
    `delete_time` int(11) DEFAULT '0' COMMENT '删除时间',
    PRIMARY KEY (`downloader_id`) USING BTREE,
    KEY `downloader_url_hash` (`downloader_url_hash`),
    KEY `file_hash` (`file_hash`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4  COMMENT='下载中心';
-- ----------------------------
-- 下载中心 END
-- ----------------------------

