<?php
namespace ctrl;

use common;

/**
 * 定时器控制器
 *
 */
class RoutineCtrl extends SysCtrlBase
{
    /**
     * 定时器ID
     *
     * @var int
     */
    private $routineId = 0;
    /**
     * 日志文件路径
     *
     * @var string
     */
    private $logFile = null;

    /**
     * 初始化
     *
     */
    public function __construct()
    {
        $this->logFile = common\Utils::mergePath(ROOT_PATH, 'data', 'Routine.'.$this->routineId.'.log');
    }

    /**
     * 写入日志
     *
     * @param string $content
     */
    private function writeLog($content)
    {
        error_log($content, 3, $this->logFile);
    }

    /**
     * 定时器分发和执行
     *
     * @return \common\GameView|null
     */
    public function doRoutine()
    {
        $routineId = $this->getInteger(0, true);
        $routineMethod = 'execRoutine'.$routineId;

        if (method_exists($this, $routineMethod))
        {
            $this->routineId = $routineId;
            return $this->$routineMethod();
        }

        return null;
    }

    /**
     * 发送数据库中缓存的下行命令
     *
     * @return \common\GameView|null
     */
    private function execRoutine1()
    {
        // 插入一条空消息，防止首条信息强制发给自己的干扰
        common\MessagePool::packData(OP_RET_SUCCESS, array());

        $pushMessageService = common\ServiceLocator::getPushMessageService();
        $pushMessages = $pushMessageService->getExpireMsgs($GLOBALS['NOW_TIME']);

        $messageIds = array();

        if (!empty($pushMessages))
        {
            foreach ($pushMessages as $pushMessage)
            {
                $pushMessage->pull();
                $messageIds[] = $pushMessage->id;
            }

            $pushMessageService->removeByIds($messageIds);
        }

        if (date('is', $GLOBALS['NOW_TIME']) % 1000 == 0)
        {
            common\MessagePool::packCheckOnline();
            $messageIds[] = 'getOnline';
        }

        if (!empty($messageIds))
        {
            $this->writeLog("[ ".$GLOBALS['NOW_DATE']." ] Push ".COUNT($messageIds)." Messages !");
            return new common\GameView();
        }

        return null;
    }
}
