<?php
namespace framework\driver\mysql;

/**
 * 查询构造器
 *
 * @package framework\driver\mysql
 */
class Query
{
    const SQL_TYPE_SELECT = 0;
//     const SQL_TYPE_INSERT = 1;
//     const SQL_TYPE_UPDATE = 2;
//     const SQL_TYPE_DELETE = 3;

    private $tableName = '';

    public $sqlType;
    public $select;
    public $join;
    public $where;
    public $groupBy;
    public $orderBy;
    public $having;
    public $limit;

    private $sql;
    private $param;

    public function __construct($tableName = null)
    {
        $this->tableName = $tableName;
    }

    public function init()
    {
        $this->sql     = '';
        $this->select  = '';
        $this->join    = '';
        $this->where   = '';
        $this->groupBy = '';
        $this->orderBy = '';
        $this->having  = '';
        $this->limit   = '';
        $this->param   = [];
    }

    public function subQueue()
    {
        $sub = (new self($this->tableName))->init();
        return $sub;
    }

    /**
     * @param string|array $fields
     * @param string $tableName
     * 1:'col1,col2' 可以写as
     * 2:['col1','col2'] 不要写as
     * @return \framework\driver\mysql\Query
     */
    public function select($fields = '*', $tableName = null)
    {
        $this->init();
        if ($tableName == null) $tableName = $this->tableName;

        $this->sqlType = self::SQL_TYPE_SELECT;

        $field_str     = is_array( $fields ) ? '`' . implode( '`,`', $fields ) . '`' : $fields;
        $this->select     = "SELECT {$field_str}";

        if ($tableName instanceof Query)
        {
            $this->select .= ' FROM ('.$tableName->getSql().')';
        }
        else
        {
            $this->select .= " FROM `{$tableName}`";
        }

        return $this;
    }

    /**
     * @param string|array $where
     * 1:col1=1 and col2=1 不建议 有SQL注入风险
     * 2:['col1=? and col2 = ?', [col1value,col2value]]
     * 3:[col1=>1, col2=>[1,2,3]]
     * 4:['like','col1','你好']
     * 5:[['like','col1','你好'],['like','col2','你好']] //TODO
     * @param string $type AND | OR
     * @return \framework\driver\mysql\Query
     */
    public function where($where, $type = '')
    {
        if ($type == '')
        {
            $this->where = ' WHERE';
        }
        else
        {
            $this->where .= " {$type}";
        }

        if (is_string($where))
        {
            //1
            $this->where .= " {$where}";
        }
        else
        {
            //2
            if (isset($where[0]) && is_string($where[0]) && isset($where[1]) && is_array($where[1]))
            {
                $this->where .= " {$where[0]}";
                foreach ($where[1] as $v)
                {
                    $this->param[] = $v;
                }

            }
            elseif (!isset($where[0]))  //3
            {
                $isFirst = true;
                foreach ($where as $k => $v)
                {
                    //IN
                    if (is_array($v))
                    {
                        $where = "{$k} IN(" . substr( str_repeat( '?,', count($v) ), 0, - 1 ) . ')';
                        foreach ($v as $val)
                        {
                            $this->param[] = $val;
                        }
                    }
                    else
                    {
                        $where = "`{$k}` = ?";
//                         ($isFirst ? " `{$k}` = ?" : " AND `{$k}` = ?");
                        $this->param[] = $v;
                    }
                    $this->where .= $isFirst ? " {$where}" : " AND {$where}";

                    $where = '';
                    $isFirst = false;
                }

            }
            else
            {
                if(isset($where[0]) && is_array($where[0])) //5
                {
                    $isFirst = true;
                    foreach ($where as $v)
                    {
                        //IN
                        if (is_array($v[2]))
                        {
                            $where = "`{$v[1]}` {$v[0]} (" . substr( str_repeat( '?,', count($v[2]) ), 0, - 1 ) . ')';
                            foreach ($v[2] as $val)
                            {
                                $this->param[] = $val;
                            }
                        }
                        else
                        {
                            $where = "`{$v[1]}` {$v[0]} ?";
                            $this->param[] = $v[2];
                        }

                        $this->where .= $isFirst ? " {$where}" : " AND {$where}";

                        $where = '';
                        $isFirst = false;
                    }
                }
                else
                {
                    //4
                    $this->where .= " `{$where[1]}` {$where[0]} ?";
                    $this->param[] = $where[2];
                }
            }
        }

        return $this;
    }

    public function andWhere($where)
    {
        $this->where($where, 'AND');
        return $this;
    }

    public function orWhere($where)
    {
        $this->where($where, 'OR');
        return $this;
    }

    /**
     *
     * @param string|array $groupBy
     * 1:col1,col2
     * 2:['col1','col2']
     * @return \framework\driver\mysql\Query
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = " GROUP BY";
        $this->groupBy .= is_string($groupBy) ? " $groupBy" : "`".implode('`,`', $groupBy)."`";

        return $this;
    }

    /**
     *
     * @param string|array $orderBy
     * 1:col1 ASC,col2 DESC
     * 2:['col1'=>true | asc,'col2'=>false | desc]
     * @return \framework\driver\mysql\Query
     */
    public function orderBy($orderBy)
    {
        $this->orderBy = " ORDER BY";
        if (is_string($orderBy))
        {
            $this->orderBy .= " {$orderBy}";
        }
        else
        {
            foreach ($orderBy as $k => $v)
            {
                $this->orderBy .= ($v || strtolower($v) == 'asc') ? " `{$k}` ASC" : " `{$k}` DESC";
            }
        }

        return $this;
    }

    /**
     * @param string|array $having
     * 1:col1=1 and col2=1
     * 2:[col1=>1, col2=>1]
     * 3:['like','col1','你好']
     * @return \framework\driver\mysql\Query
     */
    public function having($having)
    {
        $this->having = ' HAVING';
        if (is_string($having))
        {
            //1
            $this->having = $having;
        }
        else
        {
            //3
            if (isset($having[0]))
            {
                $this->having .= " `{$having[1]}` {$having[0]} ?";
                $this->param[] = $having[2];
            }
            else
            {
                //2
                foreach ($having as $k => $v)
                {
                    //IN
                    if (is_array($v))
                    {
                        $this->having = ' IN(' . substr( str_repeat( '?,', count($v) ), 0, - 1 ) . ')';
                        $this->param = $v;
                    }
                    else
                    {
                        $this->having .= ($this->having ? " `{$k}` = ?" : " AND `{$k}` = ?");
                        $this->param[] = $v;
                    }
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param int $arg1
     * @param int $arg2
     * @return \framework\driver\mysql\Query
     */
    public function limit($arg1, $arg2 = null)
    {
        $this->limit = ($arg2 == null) ? " LIMIT {$arg1}" : " LIMIT {$arg1},{$arg2}";

        return $this;
    }

    /**
     *
     * @param string $joinType    'left'|'right'
     * @param string $tableName
     * @param string $on
     * @return \framework\driver\mysql\Query
     */
    private function join($joinType, $tableName, $on)
    {
        $this->join = " {$joinType}JOIN {$tableName} ON {$on}";

        return $this;
    }

    /**
     *
     * @param string $tableName
     * @param string $on
     * @return \framework\driver\mysql\Query
     */
    public function leftJoin($tableName, $on)
    {
        return $this->join('LEFT', $tableName, $on);
    }

    /**
     *
     * @param string $tableName
     * @param string $on
     * @return \framework\driver\mysql\Query
     */
    public function rightJoin($tableName, $on)
    {
        return $this->join('RIGHT', $tableName, $on);
    }


    public function getSql()
    {
        switch ($this->sqlType)
        {
            case self::SQL_TYPE_SELECT :
                $this->sql .= ($this->select. $this->join . $this->where . $this->groupBy . $this->having . $this->orderBy . $this->limit);
                break;
        }
        return $this->sql;
    }

    public function getParam()
    {
        return $this->param;
    }
}
