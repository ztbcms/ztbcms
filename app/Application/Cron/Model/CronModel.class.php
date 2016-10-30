<?php

// +----------------------------------------------------------------------
// | 计划任务
// +----------------------------------------------------------------------

namespace Cron\Model;

use Common\Model\Model;

class CronModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('subject', 'require', '计划任务名称不能为空！', 1, 'regex', 3),
		array('loop_type', 'require', '计划任务类型不能为空！', 1, 'regex', 3),
		array('cron_file', 'require', '计划任务执行文件不能为空！', 1, 'regex', 3),
	);
	//自动完成
	protected $_auto = array(
		//array(填充字段,填充内容,填充条件,附加规则)
		//array("created_time", "time", 1, "function"), //创建时间自动填充
	);

	//计划任务循环类型
	public function loop_type(&$data) {
		//计划任务循环类型
		$loop_type = $data['loop_type'];
		switch ($loop_type) {
			case 'month':
				//月份
				$day = $data['month_day'];
				//几点
				$hour = $data['month_hour'];
				//获取 计划任务 下一次执行时间
				$nexttime = $this->getNextTime('month', $day, $hour);
				$data['next_time'] = $nexttime;
				//循环类型时间（日-时-分）
				$data['loop_daytime'] = $day . '-' . $hour . '-0';
				break;
			case 'week':
				$day = $data['week_day'];
				$hour = $data['week_hour'];
				//获取 计划任务 下一次执行时间
				$nexttime = $this->getNextTime('week', $day, $hour);
				$data['next_time'] = $nexttime;
				//循环类型时间（日-时-分）
				$data['loop_daytime'] = $day . '-' . $hour . '-0';
				break;
			case 'day':
				$hour = $data['day_hour'];
				$nexttime = $this->getNextTime('day', 0, $hour);
				$data['next_time'] = $nexttime;
				//循环类型时间（日-时-分）
				$data['loop_daytime'] = '0-' . $hour . '-0';
				break;
			case 'hour':
				$minute = $data['hour_minute'];
				//获取 计划任务 下一次执行时间
				$nexttime = $this->getNextTime('hour', 0, 0, $minute);
				$data['next_time'] = $nexttime;
				$data['loop_daytime'] = '0-0-' . $minute;
				break;
			case 'now':
				$time = (int) $data['now_time'];
				$type = $data['now_type'];
				if (!$time) {
					$this->error = "间隔时间有误！";
					return false;
				}
				$minute = $type == 'minute' ? $time : 0;
				$hour = $type == 'hour' ? $time : 0;
				$day = $type == 'day' ? $time : 0;
				$nexttime = $this->getNextTime('now', $day, $hour, $minute);
				$data['next_time'] = $nexttime;
				$data['loop_daytime'] = $day . '-' . $hour . '-' . $minute;
				break;
			default:
				$this->error = "计划任务循环类型有误！";
				return false;
		}

		return $data;
	}

	/**
	 * 添加计划任务
	 * @param type $data
	 */
	public function CronAdd($data) {
		if (!$data || !is_array($data)) {
			$this->error = "数据有误！";
			return false;
		}
		//计划任务循环类型
		$data = $this->loop_type($data);
		if ($data == false) {
			return false;
		}
		//计划任务类型处理
		$type = $data['type'] = (int) $data['type'];
		switch ($type) {
			case 1:
				if (!$data['catid']) {
					$this->error = "请选择需要刷新的栏目！";
					return false;
				}
				$catid = implode(",", $data['catid']);
				unset($data['catid']);
				$data['data'] = $catid;
				$data['cron_file'] = 'CMSRefresh_category';
				break;
			case 2:
				if (!$data['tempid']) {
					$this->error = "请选择需要刷新自定义页面！";
					return false;
				}
				$tempid = implode(",", $data['tempid']);
				unset($data['tempid']);
				$data['data'] = $tempid;
				$data['cron_file'] = 'CMSRefresh_custompage';
				break;
			case 3:
				$data['cron_file'] = 'CMSRefresh_index';
				$data['data'] = "";
				break;
			default:
				$data['data'] = "";
				unset($data['catid'], $data['tempid']);
		}
		$data = $this->create($data);
		if (!$data) {
			return false;
		}
		$cron_id = $this->add($data);
		if ($cron_id) {
			return $cron_id;
		}
		$this->error = "计划任务添加失败！";
		return false;
	}

	/**
	 * 添加计划任务
	 * @param type $data
	 */
	public function CronEdit($data) {
		if (!$data || !is_array($data)) {
			$this->error = "数据有误！";
			return false;
		}
		//计划任务循环类型
		$data = $this->loop_type($data);
		if ($data == false) {
			return false;
		}

		//计划任务类型处理
		$type = $data['type'] = (int) $data['type'];
		switch ($type) {
			case 1:
				if (!$data['catid']) {
					$this->error = "请选择需要刷新的栏目！";
					return false;
				}
				$catid = implode(",", $data['catid']);
				unset($data['catid']);
				$data['data'] = $catid;
				$data['cron_file'] = 'CMSRefresh_category';
				break;
			case 2:
				if (!$data['tempid']) {
					$this->error = "请选择需要刷新自定义页面！";
					return false;
				}
				$tempid = implode(",", $data['tempid']);
				unset($data['tempid']);
				$data['data'] = $tempid;
				$data['cron_file'] = 'CMSRefresh_custompage';
				break;
			case 3:
				$data['cron_file'] = 'CMSRefresh_index';
				$data['data'] = "";
				break;
			default:
				$data['data'] = "";
				unset($data['catid'], $data['tempid']);
		}

		$data = $this->create($data);
		if (!$data) {
			return false;
		}
		$cron_id = $this->save($data);
		if ($cron_id !== false) {
			return true;
		}
		$this->error = "计划任务添修改失败！";
		return false;
	}

	/**
	 * 获得下次执行时间
	 * @param string $loopType month/week/day/hour/now
	 * @param int $day 几号， 如果是99表示当月最后一天
	 * @param int $hour 几点
	 * @param int $minute 每小时的几分
	 */
	public function getNextTime($loopType, $day = 0, $hour = 0, $minute = 0) {
		$time = time();
		$_minute = intval(date('i', $time));
		$_hour = date('G', $time);
		$_day = date('j', $time);
		$_week = date('w', $time);
		$_mouth = date('n', $time);
		$_year = date('Y', $time);
		$nexttime = mktime($_hour, 0, 0, $_mouth, $_day, $_year);
		switch ($loopType) {
			case 'month':
				//是否闰年
				$isLeapYear = date('L', $time);
				//获得天数
				$mouthDays = $this->_getMouthDays($_month, $isLeapYear);
				//最后一天
				if ($day == 99) {
					$day = $mouthDays;
				}

				$nexttime += ($hour < $_hour ? -($_hour - $hour) : $hour - $_hour) * 3600;
				if ($hour <= $_hour && $day == $_day) {
					$nexttime += ($mouthDays - $_day + $day) * 86400;
				} else {
					$nexttime += ($day < $_day ? $mouthDays - $_day + $day : $day - $_day) * 86400;
				}
				break;
			case 'week':
				$nexttime += ($hour < $_hour ? -($_hour - $hour) : $hour - $_hour) * 3600;
				if ($hour <= $_hour && $day == $_week) {
					$nexttime += (7 - $_week + $day) * 86400;
				} else {
					$nexttime += ($day < $_week ? 7 - $_week + $day : $day - $_week) * 86400;
				}
				break;
			case 'day':
				$nexttime += ($hour < $_hour ? -($_hour - $hour) : $hour - $_hour) * 3600;
				if ($hour <= $_hour) {
					$nexttime += 86400;
				}
				break;
			case 'hour':
				$nexttime += $minute < $_minute ? 3600 + $minute * 60 : $minute * 60;
				break;
			case 'now':
				$nexttime = mktime($_hour, $_minute, 0, $_mouth, $_day, $_year);
				$_time = $day * 24 * 60;
				$_time += $hour * 60;
				$_time += $minute;
				$_time = $_time * 60;
				$nexttime += $_time;
				break;
		}
		return $nexttime;
	}

	/**
	 * 获取该月天数
	 * @param type $month 月份
	 * @param type $isLeapYear 是否为闰年
	 * @return int
	 */
	public function _getMouthDays($month, $isLeapYear) {
		if (in_array($month, array('1', '3', '5', '7', '8', '10', '12'))) {
			$days = 31;
		} elseif ($month != 2) {
			$days = 30;
		} else {
			if ($isLeapYear) {
				$days = 29;
			} else {
				$days = 28;
			}
		}
		return $days;
	}

	//用于模板输出
	public function _getLoopType($select = '') {
		$array = array('month' => '每月', 'week' => '每周', 'day' => '每日', 'hour' => '每小时', 'now' => '每隔');
		return $select ? $array[$select] : $array;
	}

	//输出中文星期几
	public function _capitalWeek($select = 0) {
		$array = array('日', '一', '二', '三', '四', '五', '六');
		return $array[$select];
	}

	//可用计划任务执行文件
	public function _getCronFileList() {
		$dir = APP_PATH . "Cron/CronScript";
		$Dirs = new \Dir($dir);
		$fileList = $Dirs->toArray();
		$CronFileList = array();
		foreach ((array) $fileList AS $k => $file) {
            $CronFileList[] = $file['filename'];
		}
		return $CronFileList;
	}

}
