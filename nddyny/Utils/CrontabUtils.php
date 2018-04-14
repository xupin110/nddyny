<?php
namespace nddyny\Utils;

use R;

class CrontabUtils
{

    /**
     *
     * @param string $crontab_string:
     *            0 1 2 3 4
     *            * * * * *
     *            - - - - -
     *            | | | | |
     *            | | | | +----- week (0 - 6)
     *            | | | +------- month (1 - 12)
     *            | | +--------- day (1 - 31)
     *            | +----------- hour (0 - 23)
     *            +------------- min (0 - 59)
     */
    public static function parse($crontab_string, $get_zero = false, $start_timestamp = null)
    {
        if (($date = R::isSuccess($Result = self::date($crontab_string))) === R::FALSE) {
            return $Result;
        }
        $start = isset($start_timestamp) ? $start_timestamp : time();
        $reduce_time = $start - strtotime(date('Y-m-d H:i', $start));
        
        // 只匹配一年以内的时间
        for ($i = 0; $i <= 60 * 60 * 24 * 366; $i += 60) {
            if (in_array(intval(date('j', $start + $i)), $date['dom'])) {
                if (in_array(intval(date('n', $start + $i)), $date['month'])) {
                    if (in_array(intval(date('w', $start + $i)), $date['dow'])) {
                        if (in_array(intval(date('G', $start + $i)), $date['hours'])) {
                            if (in_array(intval(date('i', $start + $i)), $date['minutes'])) {
                                if ($get_zero == false && $i == 0) {
                                    continue;
                                }
                                return R::success($i - $reduce_time);
                            }
                        }
                    }
                }
            }
        }
        return R::fail('cron_string > date + 366', R::ARGUMENT_INVALID);
    }

    private static function date($crontab_string)
    {
        if (! preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($crontab_string))) {
            return R::fail('crontab_string', R::ARGUMENT_INVALID);
        }
        $crons = preg_split("/[\s]+/i", trim($crontab_string));
        $date = array(
            'minutes' => self::getParseCronNumbers($crons[0], 0, 59),
            'hours' => self::getParseCronNumbers($crons[1], 0, 23),
            'dom' => self::getParseCronNumbers($crons[2], 1, 31),
            'month' => self::getParseCronNumbers($crons[3], 1, 12),
            'dow' => self::getParseCronNumbers($crons[4], 0, 6)
        );
        return R::success($date);
    }

    private static function getParseCronNumbers($s, $min, $max)
    {
        $result = array();
        $v = explode(',', $s);
        foreach ($v as $vv) {
            $vvv = explode('/', $vv);
            $step = empty($vvv[1]) ? 1 : $vvv[1];
            $vvvv = explode('-', $vvv[0]);
            $_min = count($vvvv) == 2 ? $vvvv[0] : ($vvv[0] == '*' ? $min : $vvv[0]);
            $_max = count($vvvv) == 2 ? $vvvv[1] : ($vvv[0] == '*' ? $max : $vvv[0]);
            for ($i = $_min; $i <= $_max; $i += $step) {
                $result[$i] = intval($i);
            }
        }
        ksort($result);
        return $result;
    }
}