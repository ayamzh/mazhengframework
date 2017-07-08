<?php
namespace framework\view;

use framework\core\ViewBase;

/**
 * JSON 视图
 *
 * @package framework\view
 */
class JSONView extends ViewBase
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
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($this->model);
    }
}
