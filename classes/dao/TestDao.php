<?php
namespace dao;


use entity\Test;

class TestDao extends MSDaoBase
{
    /**
     * 构造函数
     *
     * @return
     *
     */
    public function __construct ()
    {
        parent::__construct('entity\\Test');
    }

    public function test()
    {
//         $a = $this->getPdoHelper()->useMaster()->fetchArray($this->getQueryBuilder()->select('*')->where(['id'=>1]));
//         $data = $this->getPdoHelper()->fetchEntity($this->getQueryBuilder()->select('*')->where(['id'=>1]));
//         $this->getCacheHelper()->set('test', $data);
//         $data = $this->getCacheHelper()->get('test');
//         $a = new Query();

//         echo $a->select($a->subQueue()->select('xxx',['coll4'])->where(['xxx'=>'test']),['col1','col2'])->leftJoin('b', 'a.xxx = b.xxx')->where(['col1' => 1,'col2'=>2])->groupBy('col1')->having(['>','col3',5])->orderBy(['col2'=>'ASC'])->limit(5)->getSql();
    }

}