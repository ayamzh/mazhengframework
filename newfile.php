<?php
//工厂模式 + 单例模式
class Factory
{
    public static function createDatabase()
    {
        $db = Database::getInstance();
        return $db;
    }
}

class Database
{
    private static $db = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$db == null)
        {
            self::$db = new self();
        }
        return self::$db;
    }
}

//注册树模式
class Register
{
    protected $objects = [];

    public static function set($alias, $object)
    {
        self::$objects[$alias] = $object;
    }

    public function _unset($alias)
    {
        unset(self::$objects[$alias]);
    }

    public static function get($alias)
    {
        return self::$objects[$alias];
    }
}

//适配器模式 把不同的函数接口封装成统一API
interface IDatabase
{
    public function connect($host, $user, $password, $dbname);
}

class Mysql implements IDatabase
{
    public function connect($host, $user, $password, $dbname)
    {}
}
class Mysqli implements IDatabase
{
    public function connect($host, $user, $password, $dbname)
    {}
}

//策略模式 将一种特定的行为和算法 封装成类，以适应某些特定的上下文环境。分支逻辑 依赖导致 控制反转
interface UserStrategy
{
    public function showAd();
}
class MaleStrategy implements UserStrategy
{
    public function showAd()
    {}
}
class FamaleStrategy implements UserStrategy
{
    public function showAd()
    {}
}
class Page
{
    public $userStrategy;

    public function index()
    {
        $this->userStrategy->showAd();
    }
    public function setStrategy(UserStrategy $userStrategy)
    {
        $this->userStrategy = $userStrategy;
    }
}

$page = new Page();
if ($sex == 'male')
{
    $page->setStrategy(new MaleStrategy());
}
else
{
    $page->setStrategy(new FamaleStrategy());
}
$page->index();

//数据对象映射模式 ORM

//观察者模式 当一个对象状态发生改变时，依赖他的对象全部会收到通知，并自动更新
$event = new Event();
$event->trigger();

class Event extends EventBase
{
    public function trigger()
    {
        $this->notify();
    }
}

abstract class EventBase
{
    protected $observers = [];
    public function addObserver(Observer $observer)
    {
        self::$observers[] = $observer;
    }
    public function notify()
    {
        foreach (self::$observers as $ob)
        {
            $ob->update();
        }
    }
}

interface Observer
{
    public function update($event_info=null);
}

class Observer1 implements Observer
{
    public function update($event_info=null)
    {
        echo '逻辑1';
    }
}

$event = new Event();
$event->addObserver(new Observer1());
$event->trigger();


//原型模式 利用clone创建对象
class Canvas
{
    protected $decorators = [];
    public function init(){}
    public function draw()
    {
        $this->beforeDraw();

        $this->afterDraw();
    }

    public function addDecorator(DrawDecorator $decorator)
    {
        $this->decorators[] = $decorator;
    }

    protected function beforeDraw()
    {
        foreach ($this->decorators as $v)
        {
            $v->beforeDraw();
        }
    }

    protected function afterDraw()
    {
        $decorators = array_reverse($this->decorators);
        foreach ($decorators as $v)
        {
            $v->afterDraw();
        }
    }
}
$base = new Canvas();
$base->init();
$canvas1 = clone $base;
$canvas1->draw();

//装饰器模式  动态的修改类的功能
interface DrawDecorator
{
    public function beforeDraw(){}
    public function afterDraw(){}
}

class ColorDrawDecorator implements DrawDecorator
{
    public function beforeDraw(){}
    public function afterDraw(){}
}
class SizeDrawDecorator implements DrawDecorator
{
    public function beforeDraw(){}
    public function afterDraw(){}
}

$base = new Canvas();
$base->init();
$base->addDecorator(new ColorDrawDecorator());
$base->addDecorator(new SizeDrawDecorator());
$base->draw();

//迭代器模式
class AllUser implements \Iterator
{
    public function __construct()
    {

    }
    public function current()
    {}
    public function next()
    {}
    public function valid()
    {}
    public function rewind()
    {}
    public function key()
    {}
}

//代理模式 客户端与实体间建立一个代理对象。
