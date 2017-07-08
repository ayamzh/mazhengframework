<?php
namespace common;

use framework\core\Context;

class I18n
{
    /**
     * 语言文件目录
     *
     * @var string
     */
    const LOCALE_DIR = 'locale';
    /**
     * 默认域
     *
     * @var string
     */
    const DEFAULT_DOMAIN = 'game';

    /**
     * 单实例对象序列
     *
     * @var array
     */
    private static $instances = array();
    /**
     * 当前语言
     *
     * @var string
     */
    private static $locale;
    /**
     * 当前域
     *
     * @var string
     */
    private $domain;

    /**
     * 格式化索引
     *
     * @param string $key
     * @return string
     */
    public static function quote($key)
    {
        return '/\{'.$key.'\}/';
    }

    /**
     * 构造函数
     *
     * @param string $domain
     */
    private function __construct($domain)
    {
        $this->domain = $domain;

        bind_textdomain_codeset($this->domain, DEFAULT_CHARSET);
        bindtextdomain($this->domain, Utils::mergePath(Context::getRootPath(), self::LOCALE_DIR));
    }

    /**
     * 取得一个单实例I18n对象
     *
     * @param string $domain
     * @return I18n
     */
    public static function getInstance($domain)
    {
        if (empty(self::$locale))
        {
            self::$locale = DEFAULT_LOCALE;
            setlocale(LC_ALL, DEFAULT_LOCALE);
        }

        if (!array_key_exists($domain, self::$instances))
        {
            self::$instances[$domain] = new I18n($domain);
        }

        return self::$instances[$domain];
    }

    /**
     * 获取格式化后的文本
     *
     * @param string $key
     * @param array $params
     * @return string
     */
    public function _($key, $params = null)
    {
        $text = dgettext($this->domain, $key);

        if (empty($params))
        {
            return $text;
        }
        else
        {
            return preg_replace(array_map('common\I18n::quote', array_keys($params)), array_values($params), $text);
        }
    }

    /**
     * 获取指定域本地化后的文本
     *
     * @param string $domain
     * @param string $key
     * @param array $params
     * @return string
     */
    public static function __($domain, $key, $params = null)
    {
        $i18n = self::getInstance($domain);
        return $i18n->_($key, $params);
    }

    /**
     * 获取默认域本地化后的文本
     *
     * @param string $key
     * @param array $params
     * @return string
     */
    public static function ___($key, $params = null)
    {
        return self::__(self::DEFAULT_DOMAIN, $key, $params);
    }
}