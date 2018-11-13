<?php

namespace Eddic\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class Decimal implements Rule
{
    /**
     * 总位数
     *
     * @var int
     */
    public $total;

    /**
     * 小数点位数
     *
     * @var int
     */
    public $scale;

    /**
     * 正负
     *
     * @var bool
     */
    public $unsigned;

    /**
     * 最大值
     *
     * @var int
     */
    public $max;

    /**
     * 最小值
     *
     * @var int
     */
    public $min;

    /**
     * 错误信息
     *
     * @var string
     */
    public $message;

    public function __construct($total, $scale = 2, $unsigned = true)
    {
        $this->total = $total;
        $this->scale = $scale;
        $this->unsigned = $unsigned;
    }

    /**
     * 判断验证规则是否通过
     *
     * @param  string  $attribute
     * @param  string  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! is_string($value)) {
            $this->message = '必须是字符串';
            return false;
        }

        if (! is_numeric($value)) {
            $this->message = '必须是数字';
            return false;
        }

        if (0 == $value) {
            return true;
        }

        $this->min = $this->unsigned ? 0 : - pow(10, $this->total - $this->scale);
        $this->max = pow(10, $this->total - $this->scale);

        if ($this->min >= $value || $this->max <= $value) {
            return false;
        }

        return (bool) preg_match("/^\-?\d*(\.\d{1,{$this->scale}})?$/", $value);
    }

    /**
     * 获取验证错误消息
     *
     * @return string
     */
    public function message()
    {
        if (empty($this->message)) {
            $this->message = "必须是不多于{$this->scale}位小数，且不大于{$this->max}，不小于{$this->min}";
        }

        return ":attribute {$this->message}";
    }
}
