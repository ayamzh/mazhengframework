<?php
namespace framework\core;

use framework\exception\NewException;

/**
 * 控制器基类
 *
 * @package framework\core
 */
abstract class CtrlBase
{
    /**
     * 控制器参数
     *
     * @var array
     */
    protected $params = array();

    /**
     * 设置控制器参数
     *
     * @param array $params
     */
    function setParams($params)
    {
        $this->params = (array)$params;
    }

    /**
     * 前置过滤器
     *
     * @return boolean
     */
    abstract function beforeExec();

    /**
     * 清理器
     *
     */
    abstract function finalExec();

    /**
     * 获取整数参数
     *
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return int
     */
    protected function getInteger($key, $abs=true, $notEmpty=false)
    {
        $integer = array_key_exists($key, $this->params) ? intval($this->params[$key]) : 0;

        if ($abs)
        {
            $integer = abs($integer);
        }

        if (!empty($notEmpty) && empty($integer))
        {
            throw new NewException(NewException::CODE_ERROR_PARAMS);
        }

        return $integer;
    }

    /**
     * 获取整数数组参数
     *
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return array
     */
    protected function getIntegers($key, $abs=false, $notEmpty=false)
    {
        $integers = (array_key_exists($key, $this->params) && !empty($this->params[$key])) ? array_map('intval', (array)$this->params[$key]) : array();

        if ($abs)
        {
            $integers = array_map('abs', $integers);
        }

        if (!empty($notEmpty) && empty($integers))
        {
            throw new NewException(NewException::CODE_ERROR_PARAMS);
        }

        return $integers;
    }

    /**
     * 获取浮点数参数
     *
     * @param string $key
     * @param bool $abs 取绝对值
     * @param bool $notEmpty 非空
     * @return float
     */
    protected function getFloat($key, $abs=false, $notEmpty=false)
    {
        $float = array_key_exists($key, $this->params) ? floatval($this->params[$key]) : 0;

        if ($abs)
        {
            $float = abs($float);
        }

        if (!empty($notEmpty) && empty($float))
        {
            throw new NewException(NewException::CODE_ERROR_PARAMS);
        }

        return $float;
    }

    /**
     * 获取字符串参数
     *
     * @param string $key
     * @param bool $notEmpty 非空
     * @param int $min 最小长度
     * @param int $max 最大长度
     * @return string
     */
    protected function getString($key, $notEmpty=false, $min = 0, $max = 0)
    {
        $string = array_key_exists($key, $this->params) ? trim($this->params[$key]) : '';

        if (!empty($notEmpty) && $string == '')
        {
            throw new NewException(NewException::CODE_ERROR_PARAMS);
        }

        if ($min || $max)
        {
            $len = mb_strlen($string);
            if ($min > 0)
            {
                if ($len < $min) throw new NewException(NewException::CODE_ERROR_PARAMS);
            }
            if ($max > 0)
            {
                if ($len > $max) throw new NewException(NewException::CODE_ERROR_PARAMS);
            }
        }

        return $string;
    }

    /**
     * 获取字符串数组参数
     *
     * @param string $key
     * @param bool $notEmpty
     * @return array
     */
    protected function getStrings($key, $notEmpty=false)
    {
        $strings = (array_key_exists($key, $this->params) && !empty($this->params[$key])) ? array_map('trim', (array)$this->params[$key]) : array();

        if (!empty($notEmpty) && empty($strings))
        {
            throw new NewException(NewException::CODE_ERROR_PARAMS);
        }

        return $strings;
    }

    /**
     * 验证参数范围，参数为变长或数组
     * @param xxx $arg
     * @param xxx $range...     数组or变长参数
     * @return bool
     */
    protected function verifyParamList()
    {
        $args = func_get_args();
        if(count($args) < 2) {
            throw new NewException(NewException::CODE_ERROR_CALL, 'requireTwoArgAtLeast');
        }
        $arg = $args[0];

        $range = (is_array($args[1]) ? $args[1] : $args);
        foreach($range as $alt) {
            if($arg == $alt) {
                return true;
            }
        }
        throw new NewException(NewException::CODE_ERROR_CALL, 'invalidArg');
    }
}
