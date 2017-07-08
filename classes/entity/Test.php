<?php
namespace entity;

class Test extends EntityBase
{
    const TABLE_NAME  = 'game_test';

    const DB_NAME     = DB_ADVERT;

    const REDIS_INDEX = REDIS_INDEX_CACHE;

    /**
     * 用户ID
     * @var int
     */
    public $id;

    /**
     * 用户名
     * @var string
     */
    public $name;


    /**
     * 等级
     * @var int
     */
    public $level;

    /**
     * 创建时间
     * @var int
     */
    public $create_time;

    /**
     * 用于序列化、反序列化
     * @param bool $serialize
     * @param array(prop) $props
     */
    public function prepare($serialize, $props = null)
    {
    }
}