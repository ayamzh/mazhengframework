<?php
namespace dao;
use framework\driver\constcache;
use framework\exception\NewException;
use common\Utils;

/**
 * 静态数据基类
 */
class MSStaticDaoBase extends DaoBase
{
    protected $cacheType = true;

    /**
     * 构造函数
     */
    public function __construct ()
    {
        parent::__construct();

        $this->cacheType = true;
    }

    /**
     * 获取缓存键名
     *
     * @param int|string $arg1
     * @param
     *            int|string ...
     * @return string
     */
    protected function getCacheKey ()
    {
        $args = func_get_args();
        DEBUG_MODE and Utils::assert($args, NewException::CODE_ERROR_PARAMS, 'cacheKey');

        $cacheKey = '';

        if ($args)
        {
            $cacheKey = implode(KEY_SEPARATOR, $args);
        }

        $cacheKey = SERVER_MARK . KEY_SEPARATOR . $this->getBaseKey() . KEY_SEPARATOR . $cacheKey;

        return $cacheKey;
    }

    public function getCacheHelper()
    {
        if (! $this->cacheHelper)
        {
            $this->cacheHelper = new constcache\Helper();
        }
        return $this->cacheHelper;
    }

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
    public function readAll ($conds = null, $struct = true, $order = [])
    {
        $cacheKey = $this->getCacheKeyForAll($conds, $struct, $order);

        $list = $this->getCacheHelper()->get($cacheKey);
        if ($list)
        {
            return $list;
        }

        // 查询处理
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


        $this->getCacheHelper()->set($cacheKey, $list);

        return $list;
    }
}
