<?php
namespace framework\dispatcher;

/**
 * 控制台脚本请求分发器
 *
 * @package framework\dispatcher
 */
class ShellDispatcher extends DispatcherBase
{
    /**
     * 初始化请求分发器
     *
     */
    public function initialize()
    {
        $action = (isset($_SERVER['argv']) && count($_SERVER['argv']) > 1) ? $_SERVER['argv'][1] : 'Job.exec';

        $action    = explode('.', $action);
        $actionLen = count($action);

        for ($i = $actionLen - 1; $i >= 0; $i--)
        {
            if ($i == $actionLen - 1)
            {
                $this->methodName = $action[$i];
            }
            else
            {
                if ($this->ctrlName)
                {
                    $this->ctrlName = $action[$i] . '\\' . $this->ctrlName;
                }
                else
                {
                    $this->ctrlName = ucfirst($action[$i]);
                }

            }
        }
        $this->ctrlName   = $this->ctrlName.'Ctrl';
    }

    /**
     * 获取请求分发参数
     *
     * @return array
     */
    public function getParams()
    {
        $params = empty($_SERVER['argv']) ? array() : $_SERVER['argv'];
        for($i = 2; $i < $_SERVER['argc']; $i++) {
            list($key, $val) = explode('=', $_SERVER['argv'][$i]);
            $params[$key] = $val;
        }
        return $params;
    }
}
