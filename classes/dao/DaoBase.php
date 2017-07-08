<?php
namespace dao;
use framework\driver\mysql;
use framework\driver\redis;
use framework\exception\NewException;
use common\Utils;
use framework\driver\constcache;

abstract class DaoBase
{
    /**
     * 数据处理器实例
     *
     * @var \framework\driver\mysql\Helper
     */
    private $pdoHelper = null;

    /**
     * 数据查询构造器实例
     *
     * @var \framework\driver\mysql\Query
     */
    private $queryBuilder = null;

    /**
     * 主库从库
     * @var string
     */
    private $lib = 'master';

    /**
     * 缓存处理器实例：动态or静态
     *
     * @var \framework\driver\redis\Helper | \framework\driver\constcache\Helper
     */
    protected $cacheHelper;

    /**
     * local缓存处理器实例
     *
     * @var \framework\driver\redis\Helper
     */
    protected $localCacheHelper;

    /**
     * 实例名称
     *
     * @var string
     */
    protected $entity;

    /**
     * 缓存基础键名
     *
     * @var string
     */
    protected $baseKey;

    /**
     * 主键
     *
     * @var array
     */
    protected $primary = ['id'];

    /**
     * 是否开启缓存
     *
     * @var int
     */
    protected $cacheType = false;

    /**
     * 是否开启本地缓存
     *
     * @var int
     */
    protected $localCacheType = false;

    /**
     * 缓存有效期（单位：秒，0 永不过期）
     *
     * @var int
     */
    protected $cacheExpire = 0;

    /**
     * 本地缓存有效期（单位：秒，0 永不过期）
     *
     * @var int
     */
    protected $localCacheExp = 60;

    /**
     * ALL缓存所需的结构，缺省使用主键配置
     *
     * @var array(string)
     */
    protected $cacheAllKeys = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // $entity = ('entity' . substr(__CLASS__, 3, strlen(__CLASS__) - 6));
        $entity = get_class($this); // NOTE 不带$this则取的是本类而非子类
        $entity = ('entity' . substr($entity, 3, strlen($entity) - 6));

        $this->entity = $entity;

        $classRef = new \ReflectionClass($this->entity);
        $this->tableName  = $classRef->getConstant('TABLE_NAME');
        $this->dbName     = $classRef->getConstant('DB_NAME');
    }

    /**
     * pdoHelper 根据需要初始化（有缓存则无需要）
     *
     * @return \framework\driver\mysql\Helper
     */
    public function getPdoHelper()
    {
        if (! $this->pdoHelper)
        {
            $this->pdoHelper = new mysql\Helper($this->tableName, $this->dbName, $this->entity);
        }
        return $this->pdoHelper;
    }

    /**
     * queryBuilder 查询构造器
     * @return \framework\driver\mysql\Query
     */
    public function getQueryBuilder()
    {
        if (! $this->queryBuilder)
        {
            $this->queryBuilder = new mysql\Query($this->tableName);
        }
        return $this->queryBuilder;
    }

    public function useMaster()
    {
        $this->lib = 'master';
    }

    public function useSlave()
    {
        $this->lib = 'slave';
    }

    abstract function getCacheHelper();

    public function getLocalCacheHelper()
    {
        if (! $this->localCacheHelper)
        {
            $this->localCacheHelper = new redis\Helper(REDIS_INDEX_LOCAL_CACHE);
        }
        return $this->localCacheHelper;
    }

    /**
     * 获取基础键名
     *
     * @return string
     */
    protected function getBaseKey()
    {
        if (empty($this->baseKey))
        {
            $items = explode('\\', $this->entity);
            $this->baseKey = end($items);
        }

        return $this->baseKey;
    }

    /**
     * 获取缓存键名
     *
     * @param int|string $arg1
     * @param int|string ...
     * @return string
     */
    protected function getCacheKey()
    {
        $args = func_get_args();
        DEBUG_MODE and Utils::assert($args, NewException::CODE_ERROR_PARAMS, 'cacheKey');

        return implode(KEY_SEPARATOR, $args);
    }

    /**
     * 为 readAll 构造缓存key
     * NOTE 参数说明见 readAll
     *
     * @return string
     */
    protected function getCacheKeyForAll($conds, $struct, $order)
    {
        $cacheKey = 'all';
        if ($conds)
        {
            if (is_array($conds))
            {
                $cacheKey = array();
                foreach ($conds as $field => $values)
                {
                    $cacheKey[$field] = (is_array($values) ? implode('_', $values) : $values);
                }
                $cacheKey = Utils::cacheKeyArray($cacheKey, true);
            }
            else
            {
                $cacheKey = $conds;
            }
        }

        $opts = array();
        $struct and ($opts[] = ':');
        foreach ($order as $field => $asc)
        {
            $opts[] = ($asc ? '<' : '>') . $field;
        }
        $cacheKey = ($opts ? $this->getCacheKey($cacheKey, implode('', $opts)) : $this->getCacheKey($cacheKey));

        return $cacheKey;
    }

    /**
     * 获取实体的所有属性名称
     *
     * @return array
     */
    public function getEntityAttribs()
    {
        return array_keys(get_class_vars($this->entity));
    }

    /**
     * 构造SQL字段序列
     *
     * @param int $num
     * @return string
     */
    public function createFileds($num)
    {
        return implode(",", array_fill(0, $num, "?"));
    }
}
