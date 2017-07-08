<?php
namespace framework\driver\mysql;

use \PDO;

/**
 * PDO 数据处理类
 *
 * @package framework\driver\mysql
 */
class Helper
{
    /**
     * 连接实例
     *
     * @var \PDO
     */
    private $client;

    /**
     * 数据表名
     *
     * @var string
     */
    private $tableName;

    /**
     * 数据库名
     * @var unknown
     */
    private $dbName;

    /**
     * 类名
     *
     * @var string
     */
    private $className;

    /**
     *
     * @var \framework\driver\mysql\Query
     */
    private $query;

    /**
     * 当前数据库类型 主 从
     * @var string
     */
    private $clientType = null;

    /**
     *
     * @var \PDOStatement
     */
    private $statement = null;

    /**
     * 上次执行的SQL
     * @var string
     */
    private $lastSql = '';


    /**
     * FETCH_MODE：获取一条数据的第一个
     *
     * @var int
     */
    const FETCH_MODE_ONE = 1;
    /**
     * FETCH_MODE：获取一列数据
     *
     * @var int
     */
    const FETCH_MODE_COL = 2;
    /**
     * FETCH_MODE：获取一条数据（关联）
     *
     * @var int
     */
    const FETCH_MODE_ARR_ROW = 3;
    /**
     * FETCH_MODE：获取所有数据（关联）
     *
     * @var int
     */
    const FETCH_MODE_ARR_ALL = 4;
    /**
     * FETCH_MODE：获取一条数据（对象）
     *
     * @var int
     */
    const FETCH_MODE_OBJ_ROW = 5;
    /**
     * FETCH_MODE：获取所有数据（对象）
     *
     * @var int
     */
    const FETCH_MODE_OBJ_ALL = 6;

    /**
     * 构造函数
     *
     * @param string $className
     */
    public function __construct($tableName, $dbName, $className)
    {
        $this->className = $className;
        $this->tableName = $tableName;
        $this->dbName    = $dbName;
    }

    /**
     * 构造更新字段
     *
     * @param string $field
     * @return string
     */
    private static function updateFieldMap($field)
    {
        return '`' . $field . '`=:' . $field;
    }

    /**
     * 构造更新字段
     *
     * @param string $field
     * @return string
     */
    private static function changeFieldMap($field)
    {
        return '`' . $field . '`=`' . $field . '`+:' . $field;
    }

    /**
     * 取得连接实例
     *
     * @return \PDO
     */
    private function getClient()
    {
        if (!isset($this->client[$this->clientType]) || is_null($this->client[$this->clientType]))
        {
            $this->client[$this->clientType] = Manager::getInstance($this->dbName, $this->clientType);
        }
        return $this->client[$this->clientType];
    }

    /**
     *
     * @param string $type master|slave
     */
    private function closeConnect($type)
    {
        $this->client[$type] = null;
        Manager::deleteInstance($this->dbName, $type);
    }

    /**
     * 重置状态
     */
    private function reset()
    {
        $this->clientType = null;
//         $this->statement  = null;
    }

    public function useMaster()
    {
        $this->clientType = 'master';
        return $this;
    }

    public function useSlave()
    {
        $this->clientType = 'slave';
        return $this;
    }

    /**
     * 取得表名
     *
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->tableName))
        {
            $classRef = new \ReflectionClass($this->className);
            $this->tableName = $classRef->getConstant('TABLE_NAME');
        }

        return $this->tableName;
    }

    /**
     * 取得类名
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * 添加一条数据
     *
     * @param array|object $data
     * @param array   $fields
     * @param string  $onDuplicate
     * @param string  $tableName
     * @return int
     */
    public function insert($data, $fields = null, $onDuplicate = '', $tableName = null)
    {
        if (is_null($fields))
        {
            if (is_array($data))
            {
                $fields  = array_keys($data);
            }
            else
            {
                $fields  = array_keys(get_object_vars($data));
            }
        }

        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $tableName = $tableName ? $tableName : $this->getTableName();

        $query = "INSERT INTO `{$tableName}` ({$strFields}) VALUES ({$strValues})";

        if ($onDuplicate) $query .= 'ON DUPLICATE KEY UPDATE '. $onDuplicate;

        $params = [];

        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                $params[$field] = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                $params[$field] = $data->$field;
            }
        }


        return $this->execute($query, $params);
    }

    /**
     * 添加多条数据
     *
     * @param [array]|[object]   $data
     * @param array   $fields
     * @param string  $tableName
     * @return int
     */
    public function batchInsert($data, $fields = null, $tableName = null)
    {
        $items  = [];
        $params = [];

        $first = current($data);

        if (is_null($fields))
        {
            if (is_array($first))
            {
                $fields  = array_keys($first);
            }
            else
            {
                $fields  = array_keys(get_object_vars($first));
            }
        }

//         $fields = array_keys($first);

        if (is_array($first))
        {
            foreach ($data as $index => $val)
            {
                $index = '_'.$index;
                $items[] = '(:' . implode($index.', :', $fields) . $index. ')';
                foreach ($fields as $field)
                {
                    $params[$field.$index] = $val[$field];
                }
            }
        }
        else
        {
            foreach ($data as $index => $val)
            {
                $index = '_'.$index;
                $items[] = '(:' . implode($index.', :', $fields) . $index. ')';
                foreach ($fields as $field)
                {
                    $params[$field.$index] = $val->$field;
                }
            }
        }

        $tableName = $tableName ? $tableName : $this->getTableName();
        $query = "INSERT INTO `{$tableName}` (`" . implode('`,`', $fields) . "`) VALUES ".implode(',', $items);

        return $this->execute($query, $params);
    }

    /**
     * 替换一条数据
     *
     * @param array|object   $data
     * @param array   $fields
     * @param string  $tableName
     * @return int
     */
    public function replace($data, $fields = null, $tableName = null)
    {
        if (is_null($fields))
        {
            if (is_array($data))
            {
                $fields  = array_keys($data);
            }
            else
            {
                $fields  = array_keys(get_object_vars($data));
            }
        }

        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $tableName = $tableName ? $tableName : $this->getTableName();

        $query = "REPLACE INTO `{$tableName}` ({$strFields}) VALUES ({$strValues})";

        $params = [];

        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                $params[$field] = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                $params[$field] = $data->$field;
            }
        }

        return $this->execute($query, $params);
    }

    /**
     * 更新所有符合条件的对象
     *
     * @param array  $fields 所有待更新字段
     * @param array  $params
     * @param string $where
     * @param array  $change 需要做加减乘除运算的字段
     * @param string $tableName
     * @return bool
     */
    public function update($fields, $params, $where, $change=null, $tableName = null)
    {
        $updateFields = [];
        if ($change)
        {
            $tmpUpFields = array_map(__CLASS__ . '::changeFieldMap', $change);
            $updateFields = array_merge($updateFields, $tmpUpFields);
        }

        if ($change && $fields) $fields = array_diff($fields, $change);

        if ($fields)
        {
            $tmpUpFields = array_map(__CLASS__ . '::updateFieldMap', $fields);
            $updateFields = array_merge($updateFields, $tmpUpFields);
        }

        $strUpdateFields = implode(',', $updateFields);

        $tableName = $tableName ? $tableName : $this->getTableName();
        $query = "UPDATE `{$tableName}` SET {$strUpdateFields} WHERE {$where}";

        return $this->execute($query, $params);
    }

    /**
     * 删除所有符合条件的数据
     *
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete($where, $params = [])
    {
        if (empty($where))
        {
            return false;
        }

        $query = "DELETE FROM `{$this->getTableName()}` WHERE {$where}";

        return $this->execute($query);
    }

    public function execBySql($sql, $param = [])
    {

    }

    /**
     * 取得符合条件的第一条记录的第一个值
     *
     * @param Query $query
     * @return mixed
     */
    public function fetchValue(Query $query = null)
    {
        return $this->execute($query->getSql(), $query->getParam(), self::FETCH_MODE_ONE);
    }

    /**
     * 取得所有符合条件数据的第一列（一维数组）
     *
     * @param Query $query
     * @return array
     */
    public function fetchCol(Query $query = null)
    {
        return $this->execute($query->getSql(), $query->getParam(), self::FETCH_MODE_COL);
    }

    /**
     * 取得所有符合条件的数数组
     *
     * @param Query $query
     * @return array
     */
    public function fetchArray(Query $query = null)
    {
        return $this->execute($query->getSql(), $query->getParam(), self::FETCH_MODE_ARR_ALL);
    }

    /**
     * 取得所有符合条件的对象
     *
     * @param Query $query
     * @return array
     */
    public function fetchAll(Query $query = null)
    {
        $list  = $this->execute($query->getSql(), $query->getParam(), self::FETCH_MODE_OBJ_ALL);

        if ($list) foreach ($list as $obj)
        {
            method_exists($obj, 'prepare') and $obj->prepare(false);
        }

        return $list;
    }

    /**
     * 取得符合条件的一个对象
     *
     * @param Query $query
     * @return \stdClass
     */
    public function fetchEntity(Query $query = null)
    {
        $obj = $this->execute($query->getSql(), $query->getParam(), self::FETCH_MODE_OBJ_ROW);
        if ($obj)
        {
            method_exists($obj, 'prepare') and $obj->prepare(false);
        }

        return $obj;
    }

    /**
     * 通过SQL获取数据
     *
     * @param string $sql
     * @param array $params
     * @param int $fetchModel
     * @return array
     */
    public function fetchBySql($sql, $params = [], $fetchModel = self::FETCH_MODE_ALL)
    {
        return $this->execute($sql, $params, $fetchModel);
    }

    /**
     * 执行一条SQL语句
     * @param string $sql
     * @param array  $params
     */
    private function execute($sql, $params = [], $fetchModel = self::FETCH_MODE_OBJ_ALL)
    {
        $rawStatement = explode(' ', $sql);
        $sqlType = strtolower(trim($rawStatement[0]));

        if ($this->clientType == null)
        {
            if ($sqlType === 'select' || $sqlType === 'show')
            {
                $this->clientType = 'slave';
            }
            else
            {
                $this->clientType = 'master';
            }
        }

        $client = $this->getClient();

        try
        {
            $this->statement = $client->prepare($sql);
            $this->statement->execute($params);
        }
        catch (\PDOException $e)
        {
            // 服务端断开时重连一次
            if ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013)
            {
                $this->closeConnect($this->clientType);
                $client = $this->getClient();

                try
                {
                    $this->statement = $client->prepare($sql);
                    $rtn = $this->statement->execute($params);
                }
                catch (\PDOException $ex)
                {
                    $this->rollBackTrans();
                    throw $ex;
                }
            }
            else
            {
                $this->rollBackTrans();
                throw $e;
            }
        }

        $this->lastSql = $sql;
        $this->reset();
        if ($sqlType === 'select' || $sqlType === 'show')
        {
            return $this->fetchByMode($this->statement, $fetchModel);
        }
        elseif ($sqlType === 'update' || $sqlType === 'delete')
        {
            return $this->statement->rowCount();
        }
        elseif ($sqlType === 'insert')
        {
            if ($this->statement->rowCount() > 0)
            {
                return $client->lastInsertId();
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }

    }

    /**
     * 返回最后一条执行的 sql
     *
     * @return  string
     */
    public function getLastSql()
    {
        return $this->lastSql;
    }

    /**
     * 开始事务
     */
    public function beginTrans()
    {
        $this->clientType = 'master';

        try
        {
            $this->getClient()->beginTransaction();
        }
        catch (\PDOException $e)
        {
            // 服务端断开时重连一次
            if ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013)
            {
                $this->getClient()->beginTransaction();
            }
            else
            {
                throw $e;
            }
        }
    }

    /**
     * 提交事务
     */
    public function commitTrans()
    {
        $this->clientType = 'master';

        $this->getClient()->commit();

        $this->reset();
    }

    /**
     * 事务回滚
     */
    public function rollBackTrans()
    {
        $this->clientType = 'master';

        if ($this->getClient()->inTransaction())
        {
            $this->getClient()->rollBack();
        }

        $this->reset();
    }

    /**
     * 根据PDOStatement和$fetchMode获取结果
     *
     * @param PDOStatement $statement
     * @param $fetchMode
     * @return array | \stdClass
     */
    private function fetchByMode(\PDOStatement $statement, $fetchMode = self::FETCH_MODE_OBJ_ALL)
    {
        switch ($fetchMode) {
            case self::FETCH_MODE_ONE :
                return $statement->fetch(PDO::FETCH_COLUMN);
            case self::FETCH_MODE_COL :
                return $statement->fetchAll(PDO::FETCH_COLUMN);
            case self::FETCH_MODE_ARR_ROW :
                return $statement->fetch(PDO::FETCH_ASSOC);
            case self::FETCH_MODE_ARR_ALL :
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            case self::FETCH_MODE_OBJ_ROW :
                $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
                return $statement->fetch();
//                 return $this->typeConvert($statement, $statement->fetch());
            case self::FETCH_MODE_OBJ_ALL :
            default :
                $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
                return $statement->fetchAll();
//                 return $this->typeConvert($statement, $statement->fetchAll());
        }

        return false;
    }

    /**
     * 字段类型处理
     * @param PDOStatement $statement
     * @param object|array(object)
     * @return object|array(object)
     */
    private function typeConvert(\PDOStatement $stmt, $objs)
    {
        if($objs)
        {
            $columnCount = $stmt->columnCount();
            $types = array();
            for($i = 0; $i < $columnCount; $i++)
            {
                $meta = $stmt->getColumnMeta($i);
                if(isset($meta['native_type']))
                {
                    switch($meta['native_type'])
                    {
                    case 'LONG': // int
                    case 'SHORT': // smallint
                    case 'INT24': // mediumint
                    case 'LONGLONG': // bigint serial
                        $types[$meta['name']] = 1; // int (int), (integer)
                        break;
                    case 'NEWDECIMAL': // decimal
                    case 'FLOAT': // float
                    case 'DOUBLE': // double real
                        $types[$meta['name']] = 2; // float (float), (double), (real)
                        break;
                    default:
                        // $types[$meta['name']] = 0; // string
                        break;
                    }
                }
                else
                { // tinyint bit year bool
                    $types[$meta['name']] = 1;
                }
            }

            if(is_array($objs))
            {
                foreach($objs as $obj)
                {
                    foreach($types as $p => $type)
                    {
                        if($type == 1)
                        {
                            $obj->$p = (int)$obj->$p;
                        }
                        elseif($type == 2)
                        {
                            $obj->$p = (float)$obj->$p;
                        }
                    }
                }
            }
            else
            {
                $obj = $objs;
                foreach($types as $p => $type)
                {
                    if($type == 1)
                    {
                        $obj->$p = (int)$obj->$p;
                    }
                    elseif($type == 2)
                    {
                        $obj->$p = (float)$obj->$p;
                    }
                }
            }
        }
        return $objs;
    }

}
