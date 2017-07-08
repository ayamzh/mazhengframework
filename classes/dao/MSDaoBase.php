<?php
namespace dao;
use framework\driver\redis;
use framework\exception\NewException;
use common\Utils;

class MSDaoBase extends DaoBase
{

    /**
     * 主键是否自增
     *
     * @var bool
     */
    protected $autoInc = false;

    /**
     * 使用replace方法代替add方法
     *
     * @var bool
     */
    protected $replace = false;

    /**
     * 缓存DB编号
     *
     * @var int
     */
    protected $cacheIndex = REDIS_INDEX_CACHE;

    /**
     * 构造函数
     */
    public function __construct ()
    {
        parent::__construct();

//         $this->pdoHelper = $this->getPdoHelper();
    }

    /**
     * 获取REDIS对象(仅针对不开启自动缓存，又要使用REDIS对象的时候)
     *
     * @return \framework\driver\redis\Helper
     */
    public function getCacheHelper ()
    {
        if (empty($this->cacheHelper))
        {
            $this->cacheHelper = new redis\Helper($this->cacheIndex);
        }
        return $this->cacheHelper;
    }

    /**
     * 缓存全清，主要用于独立的Redis DB
     */
    public function flushCache ()
    {
        Utils::assert($this->cacheType, NewException::CODE_ERROR_BUG, 'noCache');
        Utils::assert($this->cacheIndex != REDIS_INDEX_CACHE, NewException::CODE_ERROR_BUG, 'cannotFlushOnMixedDB');
        $this->getCacheHelper()->flushDB();
    }

    /**
     * 获取缓存键名
     *
     * @param int|string $arg1
     * @param int|string ...
     * @return string
     */
    protected function getCacheKey ()
    {
        $args = func_get_args();
        Utils::assert($args, NewException::CODE_ERROR_PARAMS, 'cacheKey');

        $cacheKey = implode(KEY_SEPARATOR, $args);

        if ($this->entity)
        {
            $cacheKey = $this->getBaseKey() . KEY_SEPARATOR . $cacheKey;
        }

        return $cacheKey;
    }

    /**
     * 清除缓存
     */
    protected function clearCache()
    {}

    /**
     * 获取：根据主键
     *
     * @param int|string $arg1
     * @param int|string ...
     * @return \entity\XXX
     */
    public function read()
    {
        $args     = func_get_args();
        $obj      = null;
        $cacheKey = null;

        if ($this->cacheType)
        {
            $cacheKey = $this->getCacheKey(Utils::cacheKeyArray($args, false));
            $obj = $this->getCacheHelper()->objGet($cacheKey, $this->entity);
            if ($obj)
            {
                // NOTE 缓存不存在（并发清除了、或过期了）会导致update不完整；确保主键从不update
                $primary1 = $this->primary[0];
                if (! isset($obj->$primary1) || is_null($obj->$primary1) || false === $obj->$primary1)
                {
                    $obj = null;
                }
                else
                {
                    return $obj;
                }
            }
        }

        $where = [];
        foreach ($this->primary as $key)
        {
            $where[] = "`{$key}`=?";
        }
        $where = implode(' AND ', $where);

        $obj = $this->getPdoHelper()->fetchEntity($this->getQueryBuilder()->select('*')->where([$where,$args]));
        if ($obj)
        {
            Utils::assert(is_a($obj, $this->entity), NewException::CODE_ERROR_CLASS, null,
                [
                    'expect' => $this->entity,
                    'got'    => get_class($obj)
                ]
            );
        }

        if ($obj && $this->cacheType)
        {
            $this->getCacheHelper()->objSet($cacheKey, $obj, $this->cacheExpire);
        }

        return $obj;
    }

    /**
     * 读取全部记录
     *
     * @param array(field => int|string|array)|string $conds
     * @param bool $struct 是否结构化（否则为平坦的一维数组，而非主键映射）
     * @param array(field => asc?) $order 排序
     * @return array
     */
    public function readAll($conds = null, $struct = true, $order = array()) // TODO
    {
        $where = null;
        if ($conds)
        {
            if (is_array($conds))
            {
                $where = array();
                foreach ($conds as $field => $values)
                {
                    if (is_array($values))
                    {
                        $values = implode("','", $values);
                        $where[] = "`{$field}` IN ('{$values}')";
                    }
                    else
                    {
                        $where[] = "`{$field}`='{$values}'";
                    }
                }
                $where = implode(' AND ', $where);
            }
            else
            {
                $where = $conds;
            }
        }

        if ($order)
        {
            $where or ($where = ' 1 ');
            $tmp = array();
            foreach ($order as $field => $asc)
            {
                $tmp[] = $field . ' ' . ($asc ? 'ASC' : 'DESC');
            }
            $where .= (' ORDER BY ' . implode(', ', $tmp));
        }

        $list = ($where ? $this->getPdoHelper()->fetchAll($this->getQueryBuilder()->select('*')->where($where)) : $this->getPdoHelper()->fetchAll($this->getQueryBuilder()->select('*')));

        if ($list)
        {
            $obj = reset($list);
            Utils::assert(is_a($obj, $this->entity), NewException::CODE_ERROR_CLASS, null,
                [
                    'expect' => $this->entity,
                    'got'    => get_class($obj)
                ]);

            if ($struct)
            {
                $tmp = array();
                $keys = ($this->cacheAllKeys ? $this->cacheAllKeys : $this->primary);
                $key0 = $keys[0];
                switch (count($keys))
                { // NOTE 最多3个主键
                    case 1:
                        foreach ($list as $obj)
                        {
                            $tmp[$obj->$key0] = $obj;
                        }
                        break;
                    case 2:
                        $key1 = $keys[1];
                        foreach ($list as $obj)
                        {
                            $tmp[$obj->$key0][$obj->$key1] = $obj;
                        }
                        break;
                    case 3:
                        $key1 = $keys[1];
                        $key2 = $keys[2];
                        foreach ($list as $obj)
                        {
                            $tmp[$obj->$key0][$obj->$key1][$obj->$key2] = $obj;
                        }
                        break;
                    default:
                        Utils::assert(false, NewException::CODE_ERROR_CONFIG);
                }
                $list = $tmp;
            }
        }

        return $list;
    }

    /**
     * 新增实体记录
     *
     * @param \entity\XXX $obj
     */
    public function add($obj)
    {
        $newObj = $obj; // NOTE newObj用于自动序列化某些格式化属性而不影响外部对象、cache对象（$obj）

        if (method_exists($newObj, 'prepare'))
        {
            $newObj = clone $newObj;
            $newObj->prepare(true);
        }

        $ret = ($this->replace ? $this->getPdoHelper()->replace($newObj,
                $this->getEntityAttribs()) : $this->getPdoHelper()->insert($newObj,
                $this->getEntityAttribs()));

        if ($this->autoInc)
        {
            $primary = reset($this->primary);
            $obj->$primary = $ret;
        }

        $this->clearCache($obj);

        if ($this->cacheType)
        {
            $cacheKey = array();
            foreach ($this->primary as $pk)
            {
                $cacheKey[] = $obj->$pk;
            }

            $cacheKey = $this->getCacheKey(Utils::cacheKeyArray($cacheKey));
            $this->getCacheHelper()->objSet($cacheKey, $obj, $this->cacheExpire, $this->entity);
        }

        return $ret;
    }

    /**
     * 删除实体记录
     *
     * @param \entity\XXX $obj
     */
    public function remove($obj)
    {
        Utils::assert(is_a($obj, $this->entity), NewException::CODE_ERROR_CLASS, null,
            [
                'expect' => $this->entity,
                'got'    => get_class($obj)
            ]);

        $params = array();
        $where = array();
        foreach ($this->primary as $key)
        {
            $params[] = $obj->$key;
            $where[] = "`{$key}`=?";
        }
        $where = implode(' AND ', $where);

        $ret = $this->getPdoHelper()->remove($where, $params);

        if ($this->cacheType)
        {
            $cacheKey = $this->getCacheKey(Utils::cacheKeyArray($params, false));
            $this->getCacheHelper()->delete($cacheKey);
        }

        $this->clearCache($obj);

        return $ret;
    }

    /**
     * 更新
     *
     * @param \entity\XXX $obj
     * @param array $props
     * @param array $where 不依赖于主键
     */
    public function update($obj, $props, $where = null)
    {
        if (! $props)
        {
            return;
        }

        $newObj = $obj; // NOTE newObj用于自动序列化某些格式化属性而不影响外部对象、cache对象（$obj）

        if (method_exists($newObj, 'prepare'))
        {
            $newObj = clone $newObj;
            $newObj->prepare(true, $props);
        }

        Utils::assert(is_a($newObj, $this->entity), NewException::CODE_ERROR_CLASS, null,
            [
                'expect' => $this->entity,
                'got'    => get_class($newObj)
            ]);

        $params = [];
        //获取change字段
        $change = $newObj->getChange();

        foreach ($props as $prop)
        {
            if (isset($change[$prop]))
            {
                $params[$prop] = $change[$prop];
            }
            else
            {
                $params[$prop] = $newObj->$prop;
            }
        }

        if (is_null($where))
        {
            $where = [];
            foreach ($this->primary as $key)
            {
                $params[$key] = $newObj->$key;
                $where[] = "`{$key}`=:{$key}";
            }
            $where = implode(' AND ', $where);
        }

        $change = is_array($change) ? array_keys($change) : null;
        $ret = $this->getPdoHelper()->update($props, $params, $where, $change);

        if ($this->cacheType)
        {
            $cacheKey = array();
            foreach ($this->primary as $pk)
            {
                $cacheKey[] = $obj->$pk;
            }
            $cacheKey = $this->getCacheKey(Utils::cacheKeyArray($cacheKey));
            $this->getCacheHelper()->objUpdate($cacheKey, $obj, $props, $this->cacheExpire);
        }

        $this->clearCache($obj);

        return $ret;
    }

}
