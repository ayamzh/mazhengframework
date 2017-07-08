<?php
namespace framework\core;

use framework\exception\NewException;
use framework\view;

/**
 * 框架上下文
 *
 * @package framework\core
 */
class Context
{
    /**
     * 控制器命名空间
     *
     * @var String
     */
    const CTRL_NAMESPACE = 'ctrl';

    /**
     * 项目根路径
     *
     * @var String
     */
    private static $rootPath;
    /**
     * 框架代码路径
     *
     * @var String
     */
    private static $frameworkPath;
    /**
     * 项目配置路径
     *
     * @var String
     */
    private static $configPath;
    /**
     * 项目代码路径
     *
     * @var String
     */
    private static $classesPath;

    /**
     * 设置项目根路径
     *
     * @param String $rootPath
     * @return String
     */
    private static function setRootPath($rootPath)
    {
        self::$rootPath = $rootPath;
    }

    /**
     * 获取项目根路径
     *
     * @return String
     */
    public static function getRootPath()
    {
        if (empty(self::$rootPath))
        {
            throw new NewException(NewException::CODE_UNINITIALIZE);
        }

        return self::$rootPath;
    }

    /**
     * 获取框架代码路径
     *
     * @return String
     */
    public static function getFrameworkPath()
    {
        if (empty(self::$frameworkPath))
        {
            self::$frameworkPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
        }

        return self::$frameworkPath;
    }

    /**
     * 获取项目配置路径
     *
     * @return String
     */
    public static function getConfigPath()
    {
        if (empty(self::$configPath))
        {
            self::$configPath = self::getRootPath().DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR;
        }

        return self::$configPath;
    }

    /**
     * 获取项目代码路径
     *
     * @return String
     */
    public static function getClassesPath()
    {
        if (empty(self::$classesPath))
        {
            self::$classesPath = self::getRootPath() .DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;
        }

        return self::$classesPath;
    }

    /**
     * 格式化异常
     *
     * @param \Exception $exception
     * @return array
     */
    public static function formatException(\Exception $exception)
    {
        $exceptionHash = array(
            'className' => 'Exception',
            'message'   => $exception->getMessage(),
            'code'      => $exception->getCode(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'trace'     => array()
        );

        if ($exception instanceof NewException)
        {
            $exceptionHash['data'] = $exception->getData();
        }

        $traceItems = $exception->getTrace();

        foreach ($traceItems as $traceItem)
        {
            $traceHash = array(
                'file' => $traceItem['file'],
                'line' => $traceItem['line'],
                'function' => $traceItem['function'],
                'args' => array()
            );

            if (!empty($traceItem['class']))
            {
                $traceHash['class'] = $traceItem['class'];
            }

            if (!empty($traceItem['type']))
            {
                $traceHash['type'] = $traceItem['type'];
            }

            if (!empty($traceItem['args']))
            {
                foreach ($traceItem['args'] as $argsItem)
                {
                    $traceHash['args'][] = $argsItem;
                }
            }

            $exceptionHash['trace'][] = $traceHash;
        }

        return $exceptionHash;
    }

    /**
     * 初始化上下文
     *
     * @param string $rootPath
     */
    public static function initialize($rootPath)
    {
        self::setRootPath($rootPath);

        $configFiles = glob(self::getConfigPath().'*.php');

        foreach ($configFiles as $configFile)
        {
            require_once $configFile;
        }

        $configFiles = glob(self::getConfigPath().ENV_MODE.DIRECTORY_SEPARATOR.'*.php');

        foreach ($configFiles as $configFile)
        {
            require_once $configFile;
        }

        set_exception_handler(function($exception)
        {
            $exceptionHash = Context::formatException($exception);
            $exceptionView = new view\JSONView($exceptionHash);
            $exceptionView->display();
        }
        );
    }
}
