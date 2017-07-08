<?php
namespace framework\driver\memcache;

use \Memcached;

/**
 * Memcached 数据处理类
 *
 * @package framework\driver\memcache
 */
class Helper
{
    /**
     * 连接实例
     *
     * @var \Memcached
     */
    private $client;

    /**
     * 构造函数
     *
     */
    public function __construct()
    {
        $this->client = Manager::getInstance();
    }

    /**
     * 取得连接实例
     *
     * @return \Memcached
     */
    function getClient()
    {
        return $this->client;
    }

    /**
     * 添加指定键名的数据
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function set($key, $value, $expiration=0)
    {
        return $this->client->set($key, $value, $expiration);
    }

    /**
     * 获取指定键名的数据
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->client->get($key);
    }

    /**
     * 删除指定键名的数据
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->client->delete($key);
    }

    /**
     * 清空缓存
     *
     * @return bool
     */
    public function flush()
    {
        return $this->client->flush();
    }

    /**
     * 获取服务器统计信息
     *
     * @return array
     */
    public function stat()
    {
        return $this->client->getStats();
    }
}