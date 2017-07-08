<?php
namespace framework\driver\tokyoTyrant;

use \TokyoTyrant;

/**
 * TokyoTyrant管理工具，用于管理TokyoTyrant对象的工具，需要tokyotyrant扩展。
 *
 * @package framework\data\tt
 */
class Manager
{
    /**
     * TokyoTyrant配置
     *
     * @var <TokyoTyrantConfiguration>array
     */
    private static $configs;
    /**
     * TokyoTyrant实例
     *
     * @var <\TokyoTyrant>array
     */
    private static $instances;

    /**
     * 添加TokyoTyrant配置
     *
     * @param string $name
     * @param TokyoTyrantConfiguration $config
     */
    public static function addConfig($name, $config)
    {
        self::$configs[$name] = $config;
    }

    /**
     * 获取TokyoTyrant实例
     *
     * @param string $name
     * @return \TokyoTyrant
     */
    public static function getInstance($name)
    {
        if (empty(self::$instances[$name]))
        {
            if (empty(self::$configs[$name]))
            {
                return null;
            }

            $config = self::$configs[$name];
            $tokyoTyrant = new \TokyoTyrant($config->host, $config->port);

            self::$instances[$name] = $tokyoTyrant;
        }

        return self::$instances[$name];
    }
}