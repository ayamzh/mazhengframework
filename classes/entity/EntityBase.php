<?php
namespace entity;

class EntityBase
{
    private $change = null;

    /**
     * 设置更新字段
     * @param string $prop
     * @param int    $val
     */
    public function __call($prop, $arg)
    {
        $this->$prop         += $arg[0];
        $this->change[$prop]  = $arg[0];
    }

    /**
     * 获取更新字段：每次后清除，防止影响连续更新
     * @return array
     */
    public function getChange()
    {
        $tmpChange = $this->change;
        $this->change = null;
        return $tmpChange;
    }
}