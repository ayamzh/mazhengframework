<?php
namespace framework\dispatcher;

use framework\core\ViewBase;
use framework\core\CtrlBase;
use framework\core\Context;

/**
 * 请求分发器基类
 *
 * @package framework\dispatcher
 */
abstract class DispatcherBase
{
    /**
     * 控制器类名
     *
     * @var string
     */
    protected $ctrlName;

    /**
     * 控制器方法名
     *
     * @var string
     */
    protected $methodName;

    /**
     * 初始化请求分发器
     *
     */
    abstract function initialize();

    /**
     * 获取请求分发参数
     *
     * @return array
     */
    abstract function getParams();

    /**
     * 获取控制器类名
     *
     * @return String
     */
    public function getCtrlName()
    {
        return $this->ctrlName;
    }

    /**
     * 获取控制器方法名
     *
     * @return String
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * 分发请求
     *
     * @throws \Exception
     */
    public function dispatch()
    {
        // 初始化请求分发器
        $this->initialize();

        // 获取控制器类名和方法名
        $ctrlClass = Context::CTRL_NAMESPACE."\\".$this->getCtrlName();
        $ctrlMethod = $this->getMethodName();

        // 初始化控制器
        $ctrl = new $ctrlClass();

        // 执行前置过滤器
        $filtered = false;

        if ($ctrl instanceof CtrlBase)
        {
            $ctrl->setParams($this->getParams());
            $filtered = !$ctrl->beforeExec($this->getCtrlName());
        }

        // 执行分发方法
        $exception = null;

        if (!$filtered)
        {
            try
            {
                $view = $ctrl->$ctrlMethod();

                if ($view instanceof ViewBase)
                {
                    $view->display();
                }
            }
            catch(\Exception $e)
            {
                $exception = $e;
            }
        }

        // 执行清理器
        if ($ctrl instanceof CtrlBase)
        {
            $ctrl->finalExec();
        }

        // 抛出执行间异常
        if ($exception != null)
        {
            throw $exception;
        }
    }
}
