<?php
namespace framework\driver\mysql;

/**
 * 数据库配置信息
 *
 * @package framework\driver\mysql
 */
class Config
{
    /**
     * 主库 从库 master|slave
     *
     * @var string
     */
    public $type;
    /**
     * 数据库别名
     *
     * @var string
     */
    public $lib;
    /**
     * 数据库IP
     *
     * @var string
     */
    public $host;
    /**
     * 数据库端口
     *
     * @var int
     */
    public $port;
    /**
     * 数据库用户名
     *
     * @var String
     */
    public $user;
    /**
     * 数据库密码
     *
     * @var String
     */
    public $password;
    /**
     * 数据库名
     *
     * @var string
     */
    public $database;
    /**
     * 默认编码
     *
     * @var String
     */
    public $charset;
    /**
     * 是否长连接
     *
     * @var boolean
     */
    public $pconnect;
    /**
     * 其他属性
     *
     * @var array
     */
    public $attributes;

    /**
     * 构造函数
     *
     * @param int    $type
     * @param string $lib
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string $charset
     * @param bool   $pconnect
     * @param array  $attributes
     */
    public function __construct($type, $lib, $host, $port, $user, $password, $database, $charset = 'utf8', $pconnect = false, $attributes = [])
    {
        $this->type     = $type;
        $this->lib      = $lib;
        $this->host     = $host;
        $this->port     = $port;
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->charset  = $charset;
        $this->pconnect = $pconnect;
        $this->attributes = $attributes;
    }
}
