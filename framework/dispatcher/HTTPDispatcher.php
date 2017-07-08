<?php
namespace framework\dispatcher;

/**
 * HTTP请求分发器
 *
 * @package framework\dispatcher
 */
class HTTPDispatcher extends DispatcherBase
{
    /**
     * 路由类型：/modules/ctrl/action
     * @var int
     */
    const ROUTE_TYPE_PATH = 1;
    /**
     * 路由类型：?act='modules.ctrl.action'
     * @var int
     */
    const ROUTE_TYPE_ACT  = 2;
    /**
     * 路由类型：?op=101
     * @var int
     */
    const ROUTE_TYPE_OP   = 3;

    private $route_type   = 0;

    public function setRouteType($route_type = self::ROUTE_TYPE_PATH)
    {
        $this->route_type = $route_type;
    }

    /**
     * 初始化请求分发器
     *
     */
    public function initialize()
    {
        //接收路由信息
        switch ($this->route_type)
        {
            case self::ROUTE_TYPE_PATH:
                $action = explode('/', $_SERVER['REQUEST_URI']);
                break;

            case self::ROUTE_TYPE_ACT:
                $action    = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'Index.main';

//                 if (preg_match ( '/^([a-z_]+)\.(\w+)$/i', $action, $items))
//                 {
//                     $this->ctrlName   = $items[1].'Ctrl';
//                     $this->methodName = $items[2];
//                 }
                break;
            case self::ROUTE_TYPE_OP:
                $clientOp = intval($_REQUEST['op']);
                $action   = $GLOBALS['ACTION_MAP'][$clientOp];
                $action    = explode('.', $action);
                break;
            default:
        }

        //解析路由为ctrl action
        $actionLen = count($action);
        for ($i = $actionLen - 1; $i >= 0; $i--)
        {
            if ($i == $actionLen - 1)
            {
                $this->methodName = $action[$i];
            }
            else
            {
                if ($action[$i])
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
        return empty($_REQUEST) ? array() : $_REQUEST;
    }
}
