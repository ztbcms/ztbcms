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

    static function getLoopData($loopType, $loopData)
    {
        $nextTime = 0;
        $loopDaytime = "0-0-0";
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