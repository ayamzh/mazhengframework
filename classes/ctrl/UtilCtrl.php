<?php
namespace ctrl;
use common\GameView;
use framework\view;
use framework\driver\redis;
use framework\driver\constcache;
use common\ServiceLocator;

/**
 * 工具控制器
 */
class UtilCtrl extends UserCtrlBase
{

    /**
     * 空方法
     *
     * @return GameView
     */
    public function doNothing ()
    {
        error_log(var_export($this->params, true), 3, '/tmp/cmd.log');

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 获取OP对应关系定义
     *
     * @param bool $return
     * @return \framework\view\JSONView
     */
    public function getOpList ($return = false)
    {
        $operates = array();

        foreach ($GLOBALS['ACTION_MAP'] as $op => $action)
        {
            if ($op <= 100 or ! $action)
            {
                continue;
            }

            list ($ctrl, $method) = explode('.', $action);

            try
            {
                $methodRef = new \ReflectionMethod("ctrl\\{$ctrl}Ctrl", $method);
            } catch (\Exception $e)
            {
                continue;
            }

            $methodDoc = $methodRef->getDocComment();

            preg_match('/[ \t]+\*[ \t]*([^\*]+)\n/is', $methodDoc, $desc);
            preg_match_all(
                    '/[ \t]+\*[ \t]*@arg[ \t]*([^\s]+)[ \t]*\$?([^\s]+)[ \t]*(.*?)\n/s',
                    $methodDoc, $params);
            preg_match_all(
                    '/[ \t]+\*[ \t]*@ret[ \t]*([\d]+)[ \t]*([^\*]+)\n/is',
                    $methodDoc, $rets);

            if (empty($desc[1]))
            {
                error_log('no comments [op: ' . $op . ']');
                $desc[1] = '';
            }

            $operate = array(
                    'op' => $op,
                    'action' => $action,
                    'desc' => $desc[1],
                    'rets' => array(),
                    'params' => array()
            );

            for ($i = 0; $i < count($params[0]); $i ++)
            {
                switch (strtolower($params[1][$i]))
                {
                    case 'int':
                        $paramType = 'int';
                        break;
                    case 'float':
                        $paramType = 'Number';
                        break;
                    case 'bool':
                    case 'boolean':
                        $paramType = 'Boolean';
                        break;
                    case 'array':
                        $paramType = 'Array';
                        break;
                    default:
                        $paramType = 'String';
                }

                $operate['params'][] = array(
                        'type' => $paramType,
                        'name' => $params[2][$i],
                        'desc' => $params[3][$i]
                );
            }

            for ($i = 0; $i < count($rets[0]); $i ++)
            {
                $operate['rets'][] = array(
                        'type' => $rets[1][$i],
                        'desc' => $rets[2][$i]
                );
            }

            $index = intval($op / 100);
            $operates[$index]['label'] = $index;
            $operates[$index]['ops'][] = $operate;
        }

//         $jsOplist = Utils::mergePath(ROOT_PATH, 'node', 'oplist.json');
//         $jsOperates = json_decode(file_get_contents($jsOplist), true);

//         foreach ($jsOperates as $operate)
//         {
//             $index = intval($operate['op'] / 100);
//             $operates[$index]['label'] = $index;
//             $operates[$index]['ops'][] = $operate;
//         }

        return ($return ? $operates : new view\JSONView(array_values($operates)));
    }

    /**
     * 响应框架的PHPCmd结果（定时记录在线和注册数据）
     *
     * @return GameView
     */
    public function cmdResponse ()
    {
        $cmd = $this->getString(0, true);
        $info = json_decode($this->getString(1, true), true);

        if ($cmd == 'getPlayerCount' && ! empty($info['count']))
        {
            $registerNum = ServiceLocator::getUserService()->countRegister();

            $systemOnlineService = ServiceLocator::getSystemOnlineService();
            $systemOnlineService->add($GLOBALS['NOW_TIME'], $info['count'],
                    $registerNum);

            $gdssService = ServiceLocator::getGdssService();
            $gdssService->logOnline($info['count']);
        }

        return new GameView(OP_RET_SUCCESS);
    }

    public function flushCache ()
    {
        $constCacheHelper = new constcache\Helper();
        $constCacheHelper->flush();

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 清空缓存
     *
     * @param string $all
     *            是否全部
     * @return \framework\view\JSONView
     */
    public function clearCache ()
    {
        $all = (bool) $this->getString(2);

        $constCacheHelper = new constcache\Helper();
        $constCacheHelper->flush();

//        $redisHelper = new redis\Helper(1);
//        $redisHelper->flush();
//         $ignore = array(
//                 REDIS_INDEX_CACHE_SERVICE => 1,
//                 REDIS_INDEX_CACHE_COUNTRY => 1
//         );

         for ($i = 0; $i <= REDIS_INDEX_CACHE_MAX; $i ++)
         {
             if (($all or ! isset($ignore[$i])))
             { // NOTE
                                                                           // map不清理
                 $redisHelper = new redis\Helper($i);
                 $redisHelper->flush();
             }
         }

        return new GameView(OP_RET_SUCCESS);
    }

}
