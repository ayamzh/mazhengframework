<?php
namespace framework\driver\redis;

use framework\exception\NewException;
use common\Utils;
/**
 * Redis 数据处理类
 *
 * @package framework\driver\redis
 */
class Helper
{
    /**
     * 连接实例
     *
     * @var \Redis
     */
    private $client;

    /**
     * 构造函数
     *
     * @param int $index
     */
    public function __construct($index)
    {
        $this->client = Manager::getInstance($index);
    }

    /**
     * 取得连接实例
     *
     * @return \Redis
     */
    function getClient()
    {
        return $this->client;
    }

    /**
     * keys
     * @param string $pattern
     * @return array(string)
     */
    public function keys($pattern)
    {
        return $this->client->keys($pattern);
    }

    /**
     * 设置指定键名的数据
     *
     * @param string    $key
     * @param mixed     $value
     * @param int       $expiration
     * @return bool
     */
    public function set($key, $value, $expiration=0)
    {
        if ($expiration > 0)
        {
            return $this->client->setex($key, $expiration, $value);
        }
        else
        {
            return $this->client->set($key, $value);
        }
    }

    /**
     * 设置制定键名数据 如果存在返回false
     *
     * @param string  $key
     * @param mixed   $value
     */
    public function setNx($key, $value)
    {
        return $this->client->setnx($key, $value);
    }

    /**
     * 设置多个键名的数据
     *
     * @param array     $items <key => value>
     * @return bool
     */
    public function setMulti($items)
    {
        return $this->client->mset($items);
    }

    /**
     * 获取指定键名的数据
     *
     * @param string    $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->client->get($key);
    }

    /**
     * 获取指定键名序列的数据
     *
     * @param array     $keys
     * @return array
     */
    public function getMulti($keys)
    {
        $values = $this->client->getMultiple($keys);
        return array_combine($keys, $values);
    }

    /**
     * 增加指定键名的值并返回结果
     *
     * @param string    $key
     * @param int       $step
     * @return int
     */
    public function incrBy($key, $step=1)
    {
        return $this->client->incrBy($key, $step);
    }

    /**
     * 减少指定键名的值并返回结果
     *
     * @param string    $key
     * @param int       $step
     * @return int
     */
    public function decrBy($key, $step=1)
    {
        return $this->client->decrBy($key, $step);
    }

    /**
     * 设置指定键名的数据并返回原数据
     *
     * @param string    $key
     * @param mixed     $value
     * @return int
     */
    public function getSet($key, $value)
    {
        return $this->client->getSet($key, $value);
    }

    /**
     * 删除指定键名的数据
     *
     * @param string|array    $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->client->delete($key);
    }

    /**
     * 判断指定键名是否存在
     *
     * @param string    $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->client->exists($key);
    }

    /**
     * 设置指定哈希指定属性的数据
     *
     * @param string    $key
     * @param string    $prop
     * @param mixed     $value
     * @return bool|int    false失败 0已存在 1新加入
     */
    public function hashSet($key, $prop, $value)
    {
        return $this->client->hSet($key, $prop, $value);
    }

    /**
     * 设置指定哈希多个属性的数据
     *
     * @param string    $key
     * @param array     $props <$prop => $value>
     * @return bool
     */
    public function hashSetMulti($key, $props)
    {
        return $this->client->hMset($key, $props);
    }

    /**
     * 获取指定哈希指定属性的数据
     *
     * @param string    $key
     * @param string    $prop
     * @return bool|string    false不存在
     */
    public function hashGet($key, $prop)
    {
        return $this->client->hGet($key, $prop);
    }

    /**
     * 获取指定哈希多个属性的数据
     *
     * @param string    $key
     * @param array     $props
     * @return array    <$prop => $value>
     */
    public function hashGetMulti($key, $props)
    {
        return $this->client->hMGet($key, $props);
    }

    /**
     * 删除指定哈希指定属性的数据
     *
     * @param string    $key
     * @param string    $prop
     * @return bool
     */
    public function hashDel($key, $prop)
    {
        return (bool)$this->client->hDel($key, $prop);
    }

    /**
     * 获取指定哈希的长度
     *
     * @param string    $key
     * @return int
     */
    public function hashLength($key)
    {
        return $this->client->hLen($key);
    }

    /**
     * 获取指定哈希的所有属性
     *
     * @param string    $key
     * @return array
     */
    public function hashProps($key)
    {
        return $this->client->hKeys($key);
    }

    /**
     *获取指定哈希的所有属性的值
     *
     * @param string    $key
     * @return array
     */
    public function hashValues($key)
    {
        return $this->client->hVals($key);
    }

    /**
     * 获取指定哈希的所有属性和值
     *
     * @param string    $key
     * @return array
     */
    public function hashGetAll($key)
    {
        return $this->client->hGetAll($key);
    }

    /**
     * hash：++
     * @param string $key
     * @param string $member
     * @param int    $value
     * @return double
     */
    public function hashInc($key, $member, $value = 1)
    {
        return $this->client->hIncrBy($key, $member, $value);
    }

    /**
     * 清空当前数据库
     *
     * @return bool
     */
    public function flushDB()
    {
        return $this->client->flushDB();
    }

    /**
     * 获取服务器统计信息
     *
     * @return array
     */
    public function stat()
    {
        return $this->client->info();
    }

    /**
     * 设置过期时间（TTL）
     * @param string $key
     * @param int $seconds
     * @return bool
     */
    public function expire($key, $seconds)
    {
        return $this->client->expire($key, $seconds);
    }

    /**
     * 设置过期时间（TIMESTAMP）
     * @param string $key
     * @param int $ts
     * @return bool
     */
    public function expireAt($key, $ts)
    {
        return $this->client->expireAt($key, $ts);
    }

    /**
     * 对象设置：hash
     * @param string $key
     * @param Object $obj
     * @param int $seconds
     * @param string $class
     * @return bool
     */
    public function objSet($key, $obj, $seconds, $class = '')
    {
        Utils::assert(is_object($obj) || is_array($obj), NewException::CODE_ERROR_BUG, 'cacheNeedObject');

        if ($class)
        {
            Utils::assert(is_a($obj, $class), NewException::CODE_ERROR_BUG, 'wrongObject');
        }

        return $this->set($key, serialize($obj), $seconds);

//         $arr = [];
//         foreach($obj as $k => $v)
//         {
//             ($k{0} != "\x00") and ($arr[$k] = $v);
//         }

//         $ok = $this->client->hMset($key, $arr);

//         if($ok and $seconds) {
//             $ok = $this->client->expire($key, $seconds);
//         }
//         return $ok;
    }

    /**
     * 对象获取：hash
     * @param string $key
     * @param string $class
     * @return Object
     */
    public function objGet($key, $class = '')
    {
        $obj = $this->get($key);

        if ($obj)
        {
            $obj = unserialize($obj);
        }
//         $obj = null;
//         $arr = $this->client->hGetAll($key);
//         if($arr)
//         {
//             $obj = new $class;
//             foreach($arr as $k => $v)
//             {
//                 $obj->$k = $v;
//             }
//         }
        return $obj;
    }

    /**
     * 对象属性更新，可能全新set：hash
     * @param string $key
     * @param Object $obj
     * @param array(string) $props
     * @param int $seconds
     * @return bool
     */
    public function objUpdate($key, $obj, $props='', $seconds=0)
    {
        Utils::assert(is_object($obj) || is_array($obj), NewException::CODE_ERROR_BUG, 'cacheNeedObject');

        return $this->set($key, serialize($obj), $seconds);
        // NOTE 如果更新单个属性，可通过其命令(hSet)返回值判别
//         $tmp = array();
//         foreach($props as $prop) {
//             $tmp[$prop] = $obj->$prop;
//         }
//         $props = $tmp;

//         return $this->client->hMset($key, $props);
    }

    /**
     * 排序集合：取某范围数据
     * @param string    $key
     * @param int       $start  [1, ~]
     * @param int       $count  负数表示指定上边界
     * @param bool      $withScores
     * @param bool      $desc
     * @return array(id)|array(id => score)
     */
    public function sortRange($key, $start, $count, $withScores = false, $desc = true)
    {
        $start --;
        $end = ($count < 0 ? $count : $start + $count - 1);
        return ($desc
            ? $this->client->zRevRange($key, $start, $end, $withScores)
            : $this->client->zRange($key, $start, $end, $withScores));
    }

    /**
     * 排序集合：取某范围数据（根据分数）
     * @param string    $key
     * @param float     $min    [$min, ~]
     * @param float     $max    [~, $max]
     * @param bool      $withScores
     * @param bool      $desc
     * @param string    $open   00[] 01[) 10(] 11()
     * @return array(id)|array(id => score)
     */
    public function scoreRange($key, $min, $max, $withScores = false, $desc = true, $open = 0)
    {
        ($open & 2) and ($min = '(' . $min);
        ($open & 1) and ($max = '(' . $max);
        $opt = array('withscores' => $withScores);
        return ($desc
            ? $this->client->zRevRangeByScore($key, $min, $max, $opt)
            : $this->client->zRangeByScore($key, $min, $max, $opt));
    }

    /**
     * 排序集合：取指定成员的分数
     *
     * @param string $key
     * @param string $member
     * @return double
     */
    public function zScore($key, $member)
    {
        return $this->client->zScore($key, $member);
    }

    /**
     * 排序集合：取指定成员的排名
     *
     * @param string $key
     * @param string $member
     * @param bool   $desc
     * @return int  [1, ~]（0不存在）
     */
    public function zRank($key, $member, $desc = true)
    {
        $rank = ($desc
            ? $this->client->zRevRank($key, $member)
            : $this->client->zRank($key, $member));
        return (false === $rank ? 0 : $rank + 1);
    }

    /**
     * 排序集合：插入、更新指定成员的排名
     *
     * @param string $key
     * @param number $score
     * @param string $member
     * @return bool     是否新成员
     */
    public function zAdd($key, $score, $member)
    {
        return $this->client->zAdd($key, $score, $member);
    }

    /**
     * 排序集合：++
     * @param string $key
     * @param string $member
     * @param int    $value
     * @return double
     */
    public function zInc($key, $member, $value = 1)
    {
        return $this->client->zIncrBy($key, $value, $member);
    }

    /**
     * 排序集合：--
     * @param string $key
     * @param string $member
     * @param int    $value
     * @return double
     */
    public function zDec($key, $member, $value = 1)
    {
        return $this->client->zIncrBy($key, -$value, $member);
    }

    /**
     * 排序集合：删除
     * @param string $key
     * @param string $member
     * @return bool 是否成功
     */
    public function zRem($key, $member)
    {
        return (bool)$this->client->zRem($key, $member);
    }

    /**
     * 排序集合：总数
     * @param string $key
     * @return int
     */
    public function zLength($key)
    {
        return $this->client->zCard($key);
    }

    /**
     * 排序集合：union
     * @param string $keyDst
     * @param string $keySrc1
     * @param string $keySrc2...
     * @return int
     */
    public function zUnion()
    {
        $keys = func_get_args();
        $keyDst = array_shift($keys);
        return $this->client->zUnion($keyDst, $keys);
    }

    /**
     * 集合：add
     * @param string    $key
     * @param mixed     $member
     * @return bool     是否新成员
     */
    public function setAdd($key, $member)
    {
        return $this->client->sAdd($key, $member);
    }

    /**
     * 集合：remove
     * @param string    $key
     * @param mixed     $member
     * @return bool     是否存在
     */
    public function setRem($key, $member)
    {
        return $this->client->sRem($key, $member);
    }

    /**
     * 集合：length
     * @param string $key
     * @return int
     */
    public function setLength($key)
    {
        return $this->client->sCard($key);
    }

    /**
     * 集合：pop
     * @param string $key
     * @return string|false
     */
    public function setPop($key)
    {
        return $this->client->sPop($key);
    }

    /**
     * 集合：random member
     * @param string    $key
     * @param $count;
     * @return array|string
     */
    public function setRandMember($key, $count = 0)
    {
        $members = $this->client->sRandMember($key, $count);
        if ($count)
        {
            return ($members ? $members : array());
        }
        else
        {
            return ($members ? $members : '');
        }

    }

    /**
     * 集合：members
     * @param string    $key
     * @return array(string)
     */
    public function setMembers($key)
    {
        return $this->client->sMembers($key);
    }

    /**
     * 集合：copy
     * @param string    $keyDst
     * @param string    $keySrc
     * @return int  成员数
     */
    public function setCopy($keyDst, $keySrc)
    {
        return (int)$this->client->sUnionStore($keyDst, $keySrc);
    }

    /**
     * 集合：isMember
     * @param string    $key
     * @param mixed     $member
     * @return bool     是否在集合中
     */
    public function setIsMember($key, $member)
    {
        return $this->client->sIsMember($key, $member);
    }

    /**
     * 列表：右侧压入
     * @param string $key
     * @param string $val
     * @return int 长度
     */
    public function listRPush($key, $val)
    {
        return $this->client->rPush($key, $val);
    }

    /**
     * 列表：左侧弹出
     * @param string $key
     * @return string|nil
     */
    public function listLPop($key)
    {
        $val = $this->client->lPop($key);
        return (false === $val ? nil : $val);
    }

    /**
     * 列表：取范围
     * @param string $key
     * @param int $start    [1, ~)
     * @param int $count    负数表示指定上边界
     * @param bool $pop     是否弹出
     * @return array(string)
     */
    public function listRange($key, $start, $count, $pop = false)
    {
        $start --;
        $end = ($count < 0 ? $count : $start + $count - 1);
        $list = $this->client->lRange($key, $start, $end);
        if($pop) {
            $this->client->lTrim($key, $end + 1, -1);
        }
        return ($list ? $list : array());
    }

    /**
     * 列表：取长度
     * @param string $key
     * @return int
     */
    public function listLen($key)
    {
        return $this->client->lSize($key);
    }

}
