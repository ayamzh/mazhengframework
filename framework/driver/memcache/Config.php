<?php
namespace framework\driver\memcache;

/**
 * Memcached 配置信息
 *
 * @package framework\driver\memcache
 */
class Config
{
    /**
     * Memcached 服务器地址
     *
     * @var string
     */
    public $host;
    /**
     * Memcached 服务器端口
     *
     * @var int
     */
    public $port;

    /**
     * 构造函数
     *
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }
}
