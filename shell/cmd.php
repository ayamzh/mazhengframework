<?php
chdir(dirname(__FILE__));

use framework\dispatcher\ShellDispatcher;

require_once ("../framework/init.php");


if (empty($_SERVER['argv'][1])) exit('need params');

$dispatcher = new ShellDispatcher();
$dispatcher->dispatch();
