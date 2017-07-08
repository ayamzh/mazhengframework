<?php

function sigHandler ($sigNo)
{
    if ($sigNo == SIGINT || $sigNo == SIGHUP)
    {
        print "Interrupt Signal Catched!\n";
        $GLOBALS['JOB_HUP'] = true;
    }
}

declare(ticks = 1);

$pid = pcntl_fork();

if ($pid == - 1)
{
    die("Could Not Fork!\n");
}
else
    if ($pid)
    {
        exit();
    }

if (! posix_setsid())
{
    die("Could Not Detach From Terminal!\n");
}

pcntl_signal(SIGTERM, "sigHandler");
pcntl_signal(SIGINT, "sigHandler");
pcntl_signal(SIGHUP, "sigHandler");
pcntl_signal(SIGCHLD, "sigHandler");
pcntl_signal(SIGUSR1, "sigHandler");

chdir(dirname(__FILE__));

use framework\dispatcher\ShellDispatcher;

require_once ("../libs/framework/init.php");

$dispatcher = new ShellDispatcher();
$dispatcher->dispatch();