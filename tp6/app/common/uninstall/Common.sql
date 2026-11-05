-- ----------------------------
-- 计划任务
-- ----------------------------
DROP TABLE IF EXISTS `cms_cron`;
DROP TABLE IF EXISTS `cms_cron_log`;
DROP TABLE IF EXISTS `cms_cron_scheduling_log`;
DROP TABLE IF EXISTS `cms_cron_config`;

-- ----------------------------
-- 附件
-- ----------------------------
DROP TABLE IF EXISTS `cms_attachment_group`;
DROP TABLE IF EXISTS `cms_attachment`;
DROP TABLE IF EXISTS `cms_attachment_index`;

-- ----------------------------
-- 下载中心
-- ----------------------------
DROP TABLE IF EXISTS `cms_downloader`;

-- ----------------------------
-- 队列
-- ----------------------------
DROP TABLE IF EXISTS `cms_queue_jobs`;
DROP TABLE IF EXISTS `cms_queue_failed_jobs`;
DROP TABLE IF EXISTS `cms_queue_monitor`;
