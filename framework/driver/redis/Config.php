<?php
namespace framework\driver\redis;

/**
 * Redis 配置信息
 *
 * @package framework\driver\redis
 */
class Config
{
    /**
     * 服务器地址
     *
     * @var string
     */
    public $host;
    /**
     * 服务器端口
     *
     * @var int
     */
    public $port;
    /**
     * 默认数据库
     *
     * @var int
     */
    public $database = 0;
    /**
     * 是否序列化数据
     *
     * @var boolean
     */
    public $serialize = true;

    /**
     * 构造函数
     *
     * @param string $host
     * @param int $port
     * @param int $database
     * @param boolean $serialize
     */
    public function __construct($host, $port, $database=0, $serialize=true)
    {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->serialize = $serialize;
    }
}
