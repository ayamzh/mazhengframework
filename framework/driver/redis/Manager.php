<?php
namespace framework\driver\redis;

/**
 * Redis 连接实例管理器
 *
 * @package framework\driver\redis
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
     * @var <\Redis>array
     */
    private static $instances;

    /**
     * 添加配置
     *
     * @param int $index
     * @param Config $config
     */
    public static function addConfig($index, Config $config)
    {
        self::$configs[$index] = $config;
    }

    /**
     * 获取连接实例
     *
     * @param int $index
     * @return \Redis
     */
    public static function getInstance($index)
    {
        if (empty(self::$instances[$index]))
        {
            if (empty(self::$configs[$index]))
            {
                return null;
            }

            try
            {
                $config = self::$configs[$index];

                $redis = new \Redis();
                $redis->connect($config->host, $config->port);

                if (!empty($config->serialize))
                {
                    $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                }

                if (!empty($config->database))
                {
                    $redis->select($config->database);
                }

                self::$instances[$index] = $redis;
            }
            catch (\RedisException $exception)
            {
            }
        }

        return self::$instances[$index];
    }
}
