<?php
namespace ctrl;

use framework\core\CtrlBase;

/**
 * 系统控制器基类
 *
 */
class SysCtrlBase extends CtrlBase
{
    /**
     * 前置过滤器
     *
     * @return boolean
     */
    public function beforeExec()
    {
        return true;
    }

    /**
     * 清理器
     *
     */
    public function finalExec()
    {
    }
}
