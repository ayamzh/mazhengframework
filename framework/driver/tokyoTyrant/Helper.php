<?php
namespace framework\driver\tokyoTyrant;

use \TokyoTyrant;

/**
 * TokyoTyrant数据处理类
 *
 * @package framework\driver\tokyoTyrant
 */
class Helper
{
    /**
     * 压缩的临界长度
     *
     * @var int
     */
    const COMPRESS_LENGTH = 200;
    /**
     * 数据未压缩标志
     *
     * @var string
     */
    const FLAG_UNCOMPRESS = '0';
    /**
     * 数据压缩标志
     *
     * @var string
     */
    const FLAG_COMPRESS = '1';

    /**
     * TokyoTyrant对象
     *
     * @var \TokyoTyrant
     */
    private $tokyoTyrant;

    public function __construct($name)
    {
        $this->tokyoTyrant = Manager::getInstance($name);
    }
    
    /**
     * 存储前的预处理
     *
     * @param mixed $value
     * @return mixed
     */
    private static function pack($value)
    {
        $value = serialize($value);
        $length = strlen($value);

        return $value;
    }

    /**
     * 读取后的后续处理
     *
     * @param mixed $value
     * @return mixed
     */
    private static function unpack($value)
    {
        return unserialize($value);
    }

    /**
     * 取得TokyoTyrant对象
     *
     * @return \TokyoTyrant
     */
    function getTokyoTyrant()
    {
        return $this->tokyoTyrant;
    }

    /**
     * 设置TokyoTyrant对象
     *
     * @param \TokyoTyrant $tokyoTyrant
     */
    function setTokyoTyrant($tokyoTyrant)
    {
        $this->tokyoTyrant = $tokyoTyrant;
    }

    /**
     * 存储数据（如存在则覆盖）
     *
     * @param string $key
     * @param mixed $value
     */
    public function add($key, $value)
    {
        $this->tokyoTyrant->put($key, self::pack($value));
    }

    /**
     * 存储数据序列（如存在则覆盖）
     *
     * @param array $items
     */
    public function addMulti($items)
    {
        $items = array_map('self::pack', $items);
        $this->tokyoTyrant->put($items);
    }

    /**
     * 获取指定键名的数据
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->tokyoTyrant->get($key);
        return empty($value) ? null : self::unpack($value);
    }

    /**
     * 获取指定键名序列的数据
     *
     * @param array $keys
     * @return array
     */
    public function getMulti($keys)
    {
        $values = $this->tokyoTyrant->get($keys);
        return empty($values) ? null : array_map('self::unpack', $values);
    }

    /**
     * 获取指定前缀的索引
     *
     * @param string $prefix
     * @param int $limit
     */
    public function searchKeys($prefix, $limit)
    {
        return $this->tokyoTyrant->fwmKeys($prefix, $limit);
    }

    /**
     * 增加指定键名整数数据的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function increment($key, $offset=1)
    {
        return $this->tokyoTyrant->add($key, $offset);
    }

    /**
     * 减少指定键名整数数据的值
     *
     * @param string $key
     * @param int $offset
     * @return int
     */
    public function decrement($key, $offset=1)
    {
        return $this->tokyoTyrant->add($key, -$offset);
    }

    /**
     * 删除指定键名的数据
     *
     * @param string $key
     */
    public function delete($key)
    {
        $this->tokyoTyrant->out($key);
    }

    /**
     * 删除指定键名序列的数据
     *
     * @param array $keys
     */
    public function deleteMulti($keys)
    {
        $this->tokyoTyrant->out($keys);
    }

    /**
     * 清除所有数据（慎用）
     *
     */
    public function flush()
    {
        $this->tokyoTyrant->vanish();
    }

    /**
     * 获取服务器统计信息
     *
     * @return array
     */
    public function stat()
    {
        return $this->tokyoTyrant->stat();
    }
}
