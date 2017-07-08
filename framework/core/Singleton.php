<?php
namespace framework\core;

/**
 * 单例管理器
 *
 * @package framework\core
 */
class Singleton
{
    private static $instances = array();

    /**
     * 根据类名获取该类的单例
     *
     * @param string $className
     * @return Object
     */
    public static function get($className)
    {
        if (!array_key_exists($className, self::$instances))
        {
            self::$instances[$className] = new $className();
        }

        return self::$instances[$className];
    }

    public static function getDao($className)
    {
        return self::get("dao\\" . $className . 'Dao');
    }
}
