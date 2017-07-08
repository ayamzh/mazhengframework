<?php
require_once 'core/Context.php';

use framework\core\Context;


function sysAutoload($class)
{
    $basePath = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    $classFile = Context::getFrameworkPath().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$basePath;

    if (!file_exists($classFile))
    {
        $classFile = Context::getClassesPath().DIRECTORY_SEPARATOR.$basePath;
    }

    if (file_exists($classFile))
    {
        require_once($classFile);
    }
}
spl_autoload_register('sysAutoload');
Context::initialize(realpath(__DIR__.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);

use common\Utils;

Utils::initConfig();

set_exception_handler('common\Utils::exceptionHandler');

