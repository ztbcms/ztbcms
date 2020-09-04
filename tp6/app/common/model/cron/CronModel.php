<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-03
 * Time: 16:22.
 */

namespace app\common\model\cron;


use think\Model;

class CronModel extends Model
{

    protected $name = 'tp6_cron';
    protected $type = [
        'modified_time' => 'timestamp',
        'next_time' => 'timestamp'
    ];
    const OPEN_STATUS_ON = 1;
    const OPEN_STATUS_OFF = 0;


    public function runAction()
    {
        //载入文件
        $class = $this->cron_file;
        $start_time = time();
        $cronLog = new CronLogModel();
        $cronLog->start_time = $start_time;
        $cronLog->cron_id = $this->cron_id;

        try {
            $cron = new $class();
            $start_time = time();
            $cron->run($this->cron_id);

            //处理完成
            $end_time = time();
            $use_time = $end_time - $start_time;
            $result = CronLogModel::RESULT_SUCCESS;
            $result_msg = "ok";
        } catch (\Exception $exception) {
            //异常
            $this->errorCount++;

            $end_time = time();
            $use_time = $end_time - $start_time;
            $result = CronLogModel::RESULT_FAIL;
            $result_msg = $exception->getMessage();

        } catch (\Error $error) {
            //错误
            $this->errorCount++;

            $errorStr = $error->getMessage() . ' ' . $error->getFile() . " 第 " . $error->getLine() . " 行.\n";
            $errorStr .= $error->getTraceAsString();

            $end_time = time();
            $use_time = $end_time - $start_time;
            $result = CronLogModel::RESULT_FAIL;
            $result_msg = $errorStr;
        }

        $cronLog->end_time = $end_time;
        $cronLog->result = $result;
        $cronLog->use_time = $use_time;
        $cronLog->result_msg = $result_msg;
        $cronLog->save();

        return true;
    }

    private static function _getMouthDays($month, $isLeapYear)
    {
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

    static function getNextTime($loopType, $day, $hour, $minute = 0)
    {
        $time = time();
        $_minute = intval(date('i', $time));
        $_hour = date('G', $time);
        $_day = date('j', $time);
        $_week = date('w', $time);
        $_month = date('n', $time);
        $_year = date('Y', $time);
        $nexttime = mktime($_hour, 0, 0, $_month, $_day, $_year);
        switch ($loopType) {
            case 'month':
                //是否闰年
                $isLeapYear = date('L', $time);
                //获得天数
                $mouthDays = self::_getMouthDays($_month, $isLeapYear);
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
                $nexttime = mktime($_hour, $_minute, 0, $_month, $_day, $_year);
                $_time = $day * 24 * 60;
                $_time += $hour * 60;
                $_time += $minute;
                $_time = $_time * 60;
                $nexttime += $_time;
                break;
        }
        return $nexttime;
    }

    private static function _capitalWeek($select = 0)
    {
        $array = array('日', '一', '二', '三', '四', '五', '六');
        return $array[$select];
    }

    static function getLoopText($loopType, $loopDaytime)
    {
        $array = array('month' => '每月', 'week' => '每周', 'day' => '每日', 'hour' => '每小时', 'now' => '每隔');
        $type = $loopType ? $array[$loopType] : $array;

        list($day, $hour, $minute) = explode('-', $loopDaytime);
        if ($loopType == 'week') {
            $type .= '星期' . self::_capitalWeek($day);
        } elseif ($day == 99) {
            $type .= '最后一天';
        } else {
            $type .= $day ? $day . '日' : '';
        }
        if ($loopType == 'week' || $loopType == 'month') {
            $type .= $hour . '时';
        } else {
            $type .= $hour ? $hour . '时' : '';
        }
        $type .= $minute ? $minute . '分' : '00分';
        return $type;
    }

    static function getLoopData($loopType, $loopData)
    {
        //计划任务循环类型
        switch ($loopType) {
            case 'month':
                //月份
                $day = $loopData['month_day'];
                //几点
                $hour = $loopData['month_hour'];
                //获取 计划任务 下一次执行时间
                $nextTime = self::getNextTime('month', $day, $hour);
                //循环类型时间（日-时-分）
                $loopDaytime = $day . '-' . $hour . '-0';
                break;
            case 'week':
                $day = $loopData['week_day'];
                $hour = $loopData['week_hour'];
                //获取 计划任务 下一次执行时间
                $nextTime = self::getNextTime('week', $day, $hour);
                //循环类型时间（日-时-分）
                $loopDaytime = $day . '-' . $hour . '-0';
                break;
            case 'day':
                $hour = $loopData['day_hour'];
                $nextTime = self::getNextTime('day', 0, $hour);
                //循环类型时间（日-时-分）
                $loopDaytime = '0-' . $hour . '-0';
                break;
            case 'hour':
                $minute = $loopData['hour_minute'];
                //获取 计划任务 下一次执行时间
                $nextTime = self::getNextTime('hour', 0, 0, $minute);
                $loopDaytime = '0-0-' . $minute;
                break;
            case 'now':
                $time = (int)$loopData['now_time'];
                $type = $loopData['now_type'];
                if (!$time) {
                    return false;
                }
                $minute = $type == 'minute' ? $time : 0;
                $hour = $type == 'hour' ? $time : 0;
                $day = $type == 'day' ? $time : 0;
                $nexttime = self::getNextTime('now', $day, $hour, $minute);
                $nextTime = $nexttime;
                $loopDaytime = $day . '-' . $hour . '-' . $minute;
                break;
            default:
                return false;
        }

        return [$nextTime, $loopDaytime];
    }
}