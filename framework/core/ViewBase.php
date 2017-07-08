<?php
namespace framework\core;

/**
 * 视图基类
 *
 * @package framework\core
 */
abstract class ViewBase
{
    /**
     * 视图数据
     *
     * @var mixed
     */
    private $model;

    /**
     * 展示视图
     */
    abstract function display();
}
