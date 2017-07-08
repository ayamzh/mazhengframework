<?php
namespace framework\view;

use framework\core\ViewBase;

/**
 * 字符串视图
 *
 * @package framework\view
 */
class StringView extends ViewBase
{
    /**
     * 初始化视图
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * 展示视图
     *
     */
    public function display()
    {
        header("Content-Type:text/plain; charset=utf-8");
        echo $this->model;
    }
}
