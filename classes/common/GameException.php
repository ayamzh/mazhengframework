<?php
namespace common;

/**
 * 游戏通用非关键性异常
 *
 */
class GameException extends \Exception
{
    /**
     * ret返回值
     *
     * @var int
     */
    private $ret = 0;
    /**
     * 扩展信息
     *
     * @var array
     */
    private $data = array();

    /**
     * 初始化通用异常
     *
     * @param int $ret
     * @param string $message
     * @param array $data
     */
    public function __construct($ret, $message, $data = array())
    {
        $this->ret = $ret;
        $this->data = $data;

        parent::__construct($message, $ret);
    }

    /**
     * 获取Ret
     *
     * @return int
     */
    public function getRet()
    {
        return $this->ret;
    }

    /**
     * 获取扩展信息
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}