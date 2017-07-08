<?php
namespace common;
use framework\core\ViewBase;

/**
 * GameServer View
 *
 * @package view
 */
class GameView extends ViewBase
{

    /**
     * 初始化视图
     *
     * @param int $ret 结果值
     * @param array $data 数据
     * @param array $to 接受方
     * @param boolean $push 是否推送数据
     */
    public function __construct ($ret = OP_RET_SUCCESS, $data = array(), $to = array(), $push = true)
    {
        if ($push) $this->push($ret, $data, $to);
    }

    /**
     * 添加数据
     *
     * @param int $ret 结果值
     * @param array $data 数据
     * @param array $to 接受方
     */
    public function push ($ret, $data, $to)
    {
        MessagePool::packData($ret, $data, $to);
    }

    /**
     * 展示视图
     */
    public function display ()
    {
        $msgs = &MessagePool::getMessagesNoCopy();

        if (! $msgs)
        {
            return;
        }
        if (MessagePool::isUserOp())
        {
            echo "\0";
        }
        // echo implode("\0", array_map('json_encode',
        // MessagePool::getMessages()));
        // NOTE 避免大量消息时的字符串拼接处理
        $lastMsg = array_pop($msgs);
        foreach ($msgs as $msg)
        {
            echo json_encode($msg);
            echo "\0";
        }
        echo json_encode($lastMsg);
    }
}
