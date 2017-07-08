<?php
namespace framework\driver\mysql;

use \PDO;

/**
 * PDO 连接实例管理器
 *
 * @package framework\driver\mysql
 */
class Manager
{
    /**
     * 连接配置
     *
     * @var array(Config)
     */
    private static $config;
    /**
     * 连接实例
     *
     * @var array(\PDO)
     */
    private static $instance;

    /**
     * 添加配置
     *
     * @param Config $config
     */
    public static function addConfig(Config $config)
    {
        self::$config[$config->lib][$config->type][] = $config;
    }

    /**
     * 删除连接实例
     *
     * @param string $lib 数据库
     * @param string $type master|slave
     */
    public static function deleteInstance($lib, $type = 'master')
    {
        self::$instance[$lib][$type] = null;
    }

    /**
     * 获取连接实例
     *
     * @param string $lib 数据库
     * @param string $type master|slave
     * @return \PDO
     */
    public static function getInstance($lib, $type = 'master')
    {
        $dbIndex = array_rand(self::$config[$lib][$type]);
        if (empty(self::$instance[$lib][$type][$dbIndex]))
        {
            if (empty(self::$config[$lib][$type][$dbIndex]))
            {
                return null;//TODO 框架异常
            }

            self::$instance[$lib][$type][$dbIndex] = new \PDO(
                'mysql:host='.self::$config[$lib][$type][$dbIndex]->host.';port='.self::$config[$lib][$type][$dbIndex]->port.';dbname='.self::$config[$lib][$type][$dbIndex]->database,
                self::$config[$lib][$type][$dbIndex]->user,
                self::$config[$lib][$type][$dbIndex]->password,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '".self::$config[$lib][$type][$dbIndex]->charset."';",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                )
            );
        }

        return self::$instance[$lib][$type][$dbIndex];
    }
}
