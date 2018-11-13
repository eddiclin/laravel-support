<?php

namespace Eddic\Support;

class Time
{
    const SECONDS_OF_MINUTE = 60;
    const SECONDS_OF_HOUR = 60 * self::SECONDS_OF_MINUTE;
    const SECONDS_OF_DAY = 24 * self::SECONDS_OF_HOUR;
    const SECONDS_OF_WEEK = 7 * self::SECONDS_OF_DAY;
    const SECONDS_OF_MONTH = 30 * self::SECONDS_OF_DAY;
    const SECONDS_OF_YEAR = 365 * self::SECONDS_OF_DAY;
    
    const MINUTES_OF_HOUR = 60;
    const MINUTES_OF_DAY = 24 * self::MINUTES_OF_HOUR;
    const MINUTES_OF_WEEK = 7 * self::MINUTES_OF_DAY;
    const MINUTES_OF_MONTH = 30 * self::MINUTES_OF_DAY;
    const MINUTES_OF_YEAR = 365 * self::MINUTES_OF_DAY;
    
    /**
     * 返回当前时间的字符串
     *
     * @return string
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }
}
