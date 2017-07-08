<?php
namespace ctrl;
use common;
use entity;
use common\ServiceLocator;

/**
 * 队列控制器
 */
class JobCtrl extends SysCtrlBase
{

    /**
     * 端口绑定用Socket
     *
     * @var resource
     */
    private $socket = null;

    /**
     * Pid文件路径
     *
     * @var string
     */
    private $pidFile = null;

    /**
     * 错误日志文件路径
     *
     * @var string
     */
    private $logFile = null;

    /**
     * 预处理和检查
     */
    public function __construct ()
    {
        if (empty($_SERVER['argv']) ||
                 ! preg_match('/.*job\.php$/', $_SERVER['argv'][0]))
        {
            exit();
        }

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->logFile = common\Utils::mergePath(ROOT_PATH, 'data',
                'jobError.' . date('Ymd') . '.txt');
    }

    /**
     * 绑定端口
     *
     * @param int $port
     */
    private function bindPort ($port)
    {
        $result = @socket_bind($this->socket, '127.0.0.1', $port);

        if (empty($result))
        {
            die("Can Not Bind To Port {$port}\n");
        }
        else
        {
            socket_listen($this->socket);
            file_put_contents($this->pidFile, posix_getpid());
        }
    }

    /**
     * 写入错误日志
     *
     * @param string $content
     */
    private function writeLog ($content)
    {
        error_log($content, 3, $this->logFile);
    }

    /**
     * 执行队列主方法
     */
    public function exec ()
    {
        $this->pidFile = common\Utils::mergePath(ROOT_PATH,
                'job.' . SERVER_MARK . '.' . __FUNCTION__ . '.pid');
        $this->bindPort(JOB_BIND_PORT);

        echo "==================================================\n";
        echo "[" . $GLOBALS['NOW_DATE'] . "] Job Start!\n";
        echo "==================================================\n";

        $jobService = ServiceLocator::getJobService();

        while (true)
        {
            if (! empty($GLOBALS['JOB_HUP']))
            {
                die('Shoutdown Job On ' . date('Y-m-d H:i:s') . "!\n");
            }

            $GLOBALS['NOW_TIME'] = time();
            $GLOBALS['NOW_DATE'] = date('Y-m-d H:i:s', $GLOBALS['NOW_TIME']);

            $jobs = $jobService->getBatchJobs(100);

            if (! empty($jobs))
            {
                foreach ($jobs as $job)
                {
                    $jobStartTime = microtime(true);
                    $jobMethod = 'doJob' . $job->type;

                    try
                    {
                        if ($this->$jobMethod($job))
                        {
                            $jobService->remove($job);
                        }
                    } catch (\Exception $exception)
                    {
                        $errorMsg = "Error Time: {$GLOBALS['NOW_DATE']}\n";
                        $errorMsg .= "Job Info: " . var_export($job, true) . "\n";
                        $errorMsg .= "Recover SQL: INSERT INTO `job` (`type`, `itemId`, `extendId`, `execTime`) VALUES ('{$job->type}', '{$job->itemId}', '{$job->extendId}', '{$job->execTime}')\n";
                        $errorMsg .= "{$exception}\n\n";

                        $this->writeLog($errorMsg);

                        $jobService->remove($job);
                    }

                    $jobUseTime = microtime(true) - $jobStartTime;
                    $jobExecTime = date('Y-m-d H:i:s', $job->execTime);

                    echo "[ExecTime:{$jobExecTime}] [Type:{$job->type}] [ItemId:{$job->itemId}] [ExtendId:{$job->extendId}] [UseTime:{$jobUseTime}]\n";
                }
            }

            usleep(200);
        }
    }

    /**
     * 通缉令BOSS
     *
     * @param entity\Job $job
     */
    private function doJob1 (entity\Job $job)
    {
        return true;
    }
}
