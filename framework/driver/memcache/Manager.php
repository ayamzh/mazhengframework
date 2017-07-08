<?php
namespace framework\driver\memcache;

use \Memcached;

/**
 * Memcache 连接实例管理器
 *
 * @package framework\driver\memcache
 */
class Manager
{
    /**
     * 连接配置
     *
     * @var <Config>array
     */
    private static $configs;
    /**
     * 连接实例
     *
     * @var \Memcached
     */
    private static $instance;

    /**
     * 添加配置
     *
     * @param Config $config
     */
    public static function addConfig(Config $config)
    {
        self::$configs[] = $config;
    }

    /**
     * 获取连接实例
     *
     * @return \Memcached
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            if (empty(self::$configs))
            {
                return null;
            }

            $memcached = new \Memcached();

            foreach (self::$configs as $config)
            {
                $memcached->addServer($config->host, $config->port);
            }

            self::$instance = $memcached;
        }

        return self::$instance;
    }
}
