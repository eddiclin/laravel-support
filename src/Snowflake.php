<?php

namespace Eddic\Support;

use DateTime;
use InvalidArgumentException;

/**
 * 模仿 Twitter 的 Snowflake 算法
 *
 * 该实现存在一些问题，在 cgi 模式下解决比较麻烦，后续可以通过服务化解决
 * 注意：
 *  1. 存在时间回拨问题
 *  2. 序列号 sequence 是无序的
 *
 * Class Snowflake
 * @package Eddic\Support
 */
class Snowflake
{
    // 时间戳的基准起点：2018-01-01 00:00:00
    const EPOCH = 1514736000000;

    // 数据库标识的位数
    const DB_BITS = 5;

    // 数据表标识的位数
    const TABLE_BITS = 5;

    // 最大数据库编号：(2 ^ 5) - 1
    const MAX_DB_ID = 31;

    // 毫秒内自增数的位数
    const SEQUENCE_BITS = 12;

    // 最大序列号：(2 ^ 12) - 1
    const MAX_SEQUENCE = 4095;

    protected $dbId = 0;
    protected $tableId = 0;

    public function __construct($dbId = 0, $tableId = 0)
    {
        $max = self::MAX_DB_ID;

        if ($dbId > $max || $dbId < 0) {
            throw new InvalidArgumentException("dbId 不能大于 {$max}，或者小于 0");
        }

        if ($tableId > $max || $tableId < 0) {
            throw new InvalidArgumentException("tableId 不能大于 {$max}，或者小于 0");
        }

        $this->dbId = $dbId;
        $this->tableId = $tableId;
    }

    public function id()
    {
        $timestamp = $this->millisecond() - self::EPOCH;

        $sequence = $this->sequence();

        // 计算需要左移的位数
        $timestampLeftShift = self::SEQUENCE_BITS + self::TABLE_BITS + self::DB_BITS;

        $dbIdLeftShift = self::SEQUENCE_BITS + self::TABLE_BITS;

        $tableIdLeftShift = self::SEQUENCE_BITS;

        // 由以下 4 段数据组成: 时间戳、库编号、表编号、序列号
        $id = ($timestamp << $timestampLeftShift) |
            ($this->dbId << $dbIdLeftShift) |
            ($this->tableId << $tableIdLeftShift) | $sequence;

        return $id;
    }

    /**
     * 获取当前的毫秒时间戳
     *
     * @return int
     */
    protected function millisecond()
    {
        return intval(floor(microtime(true) * 1000));
    }

    protected function sequence()
    {
        return mt_rand(0, self::MAX_SEQUENCE);
    }

    /**
     * 将时间戳转为 id
     *
     * @param \DateTime|int $time
     * @return int
     */
    public static function idOfTime($time)
    {
        if ($time instanceof DateTime) {
            $time = $time->getTimestamp();
        }

        $timestamp = $time * 1000 - self::EPOCH;
        $timestampLeftShift = self::SEQUENCE_BITS + self::TABLE_BITS + self::DB_BITS;

        return $timestamp << $timestampLeftShift;
    }
}
