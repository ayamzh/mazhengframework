<?php
namespace common;

use framework\core\Singleton;

/**
 * 获取业务逻辑实例工具类
 *
 */
class ServiceLocator
{
    /**
     * 取得一个TestService对象
     *
     * @return \service\TestService
     */
    public static function getTestService()
    {
        return Singleton::get("service\\TestService");
    }
}
