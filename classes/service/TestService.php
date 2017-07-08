<?php
namespace service;
use framework\core\Singleton;

/**
 * TestService
 *
 * @package TestService
 */
class TestService
{

    /**
     * TestDao对象
     *
     * @var \dao\TestDao
     */
    private $testDao;

    /**
     * 构造函数
     */
    public function __construct ()
    {
        $this->testDao = Singleton::getDao("Test");
    }

    public function read()
    {
        return $this->testDao->read();
    }

}