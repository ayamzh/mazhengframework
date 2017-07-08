<?php
namespace ctrl;
use common\Utils;
use common\ServiceLocator;

/**
 * 计划任务控制器
 */
class ScheduleCtrl extends SysCtrlBase
{

    /**
     * 当前周数
     *
     * @var int
     */
    private $currWeek = null;

    /**
     * 当前日
     *
     * @var int
     */
    private $currDay = null;

    /**
     * 当前小时数
     *
     * @var int
     */
    private $currHour = null;

    /**
     * 当前分钟数
     *
     * @var int
     */
    private $currMinute = null;

    /**
     * 错误日志文件路径
     *
     * @var string
     */
    private $logFile = null;

    /**
     * 预处理和检查
     */
    public function __construct ($execTime = null)
    {
        if (! DEBUG_MODE &&
                 (empty($_SERVER['argv']) ||
                 ! preg_match('/.*cmd\.php$/', $_SERVER['argv'][0])))
        {
            exit();
        }

        if (! $execTime) $execTime = $GLOBALS['NOW_TIME'];

        $this->currWeek = date('W', $this->execTime); // 一年中的第几周 1-52
        $this->currDay = date('N', $this->execTime); // 一周中的第几天 周 一至周日 1-7
        $this->currHour = date('G', $this->execTime); // 一天中的第几小时 0-23
        $this->currMinute = date('i', $this->execTime); // 一小时中的第几分钟 0-59

        $this->logFile = Utils::mergePath(ROOT_PATH, 'webroot', 'data',
                'scheduleError.' . date('Ymd') . '.txt');
    }

    /**
     * 用于相应单个命名调用请求的魔术方法
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call ($method, $arguments)
    {
        if (! method_exists($this, $method))
        {
            return null;
        }

        return call_user_func_array(array(
                $this,
                $method
        ), array(
                $this->params
        ));
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
     * 执行计划任务并记录异常
     *
     * @param string $taskName 任务名
     * @param int|null $execWeek 周周期
     * @param int|null $execDay 日周期
     * @param int|null $execHour 小时周期
     * @param int|null $execMinute 分钟周期
     */
    private function doTask ($taskName, $execWeek, $execDay, $execHour, $execMinute)
    {
        if (($execWeek === null || $this->currWeek == $execWeek ||
            ($execWeek < 0 && $this->currWeek % abs($execWeek) == 0)) &&
            ($execDay === null || $this->currDay == $execDay ||
                ($execDay < 0 && $this->currDay % abs($execDay) == 0)) &&
            ($execHour === null || $this->currHour == $execHour ||
                ($execHour < 0 && $this->currHour % abs($execHour) == 0)) &&
            ($execMinute === null || $this->currMinute == $execMinute ||
                ($execMinute < 0 && $this->currMinute % abs($execMinute) == 0)))
        {
            try
            {
                $this->$taskName();
                $this->markEnd($taskName);
            }
            catch (\Exception $exception)
            {
                $errorMsg = "Error Time: {$GLOBALS['NOW_DATE']}\n";
                $errorMsg .= "{$exception}";

                $this->writeErrorLog($errorMsg);
            }
        }
    }

    /**
     * 定时执行的计划任务
     */
    public function exec ()
    {
        $this->doTask('testTask', null, null, null);
    }

    /**
     * 远程执行维护命令接口
     */
    public function remoteExec ()
    {
        ini_set('display_errors', 'On');

        if (empty($_SERVER['argv'][2]))
        {
            die("\n!! Command error!\n\n");
        }

        $command = $_SERVER['argv'][2];
        $cmdInfo = pathinfo($command);

        $fileName = Utils::mergePath(ROOT_PATH, 'webroot', $command);

        if ($cmdInfo['extension'] == 'php')
        {
            echo "\n++ Execute php file ... \n";

            include ($fileName);
            unlink($fileName);

            echo "++ OK\n\n";
        }
        elseif ($cmdInfo['extension'] == 'sql')
        {
            echo "\n++ Execute sql file ... \n";

            $mysqlCmd = '/usr/bin/mysql --default-character-set=utf8 -u ' .
                     DB_USER . ' -p' . DB_PASS . ' -h ' . DB_HOST . ' -P ' .
                     DB_PORT . ' ' . DB_LIBR . ' < ' . $fileName . "\n";
            passthru($mysqlCmd);
            unlink($fileName);

            echo "++ OK\n\n";
        }
        else
        {
            echo "\n++ Execute command '{$command}' ... \n";

            passthru('webexec ' . SERVER_MARK . ' ' . $command);

            echo "++ OK\n\n";
        }
    }

    /**
     * 刷新配置文件
     */
    public function renewConfig ()
    {
        $systemConfigSrv = ServiceLocator::getSystemConfigService();

        echo "\n++ Renew Database ...";

        $result = $systemConfigSrv->renewDB();

        if (empty($result))
        {
            echo "Error\n";
        }
        else
        {
            echo "OK\n";
            echo "++ Renew Config File ...";

            if ($systemConfigSrv->renewFile())
            {
                echo "OK\n\n";
            }
            else
            {
                echo "Error\n\n";
            }
        }
    }

    /**
     * 刷新关键词表
     */
    public function renewFilterWords ()
    {
//         $baseUrl = 'http://api.firingline.cn/index.php?act=system.getFilterWords&type=1&kind={kind}&style={style}&v=1.0';

//         // 更新聊天关键词表
//         $chatWords = @file_get_contents(
//                 str_replace(array(
//                         '{kind}',
//                         '{style}'
//                 ), array(
//                         2,
//                         1
//                 ), $baseUrl));

//         echo "\n++ Renew Chat Filter Words ...";

//         if (empty($chatWords))
//         {
//             echo "Error\n";
//         }
//         else
//         {
//             @file_put_contents(
//                     Utils::mergePath(ROOT_PATH, 'libs', 'filterWords',
//                             'wordfilter.txt'), $chatWords);
//             echo "OK\n";
//         }

//         // 更新项目姓名关键词表
//         $nameWords = @file_get_contents(
//                 str_replace(array(
//                         '{kind}',
//                         '{style}'
//                 ), array(
//                         1,
//                         1
//                 ), $baseUrl));

//         echo "++ Renew Name Filter Words ...";

//         if (empty($nameWords))
//         {
//             echo "Error\n";
//         }
//         else
//         {
//             $nameWords = explode(PHP_EOL, $nameWords);
//             $nameWords = array_diff($nameWords, array(
//                     null,
//                     'null',
//                     '',
//                     ' '
//             ));
//             $max = count($nameWords) - 1;
//             $start = 0;
//             $i = 0;
//             $newWords = '<?php' . PHP_EOL . '  return array(' . PHP_EOL;
//             ;
//             while ($start <= $max)
//             {
//                 $words = array_slice($nameWords, $start, 1000);
//                 $newWords = $newWords . "\t" . $i . '=>"/' .
//                          implode('|', array_map('preg_quote', $words)) . '/is",' .
//                          PHP_EOL;
//                 $start += 1000;
//                 $i ++;
//             }
//             $newWords .= ');' . PHP_EOL;
//             @file_put_contents(
//                     Utils::mergePath(ROOT_PATH, 'libs', 'filterWords',
//                             'nameWords.php'), $newWords);
//             echo "OK\n";
//         }

//         // 更新项目内容关键词表
//         $contentWords = @file_get_contents(
//                 str_replace(array(
//                         '{kind}',
//                         '{style}'
//                 ), array(
//                         2,
//                         1
//                 ), $baseUrl));

//         echo "++ Renew Content Filter Words ...";

//         if (empty($contentWords))
//         {
//             echo "Error\n\n";
//         }
//         else
//         {
//             $contentWords = explode(PHP_EOL, $contentWords);
//             $contentWords = array_diff($contentWords, array(
//                     null,
//                     'null',
//                     '',
//                     ' '
//             ));
//             $max = count($contentWords) - 1;
//             $start = 0;
//             $i = 0;
//             $newWords = '<?php' . PHP_EOL . '  return array(' . PHP_EOL;
//             ;
//             while ($start <= $max)
//             {
//                 $words = array_slice($nameWords, $start, 1000);
//                 $newWords = $newWords . "\t" . $i . '=>"/' .
//                          implode('|', array_map('preg_quote', $words)) . '/is",' .
//                          PHP_EOL;
//                 $start += 1000;
//                 $i ++;
//             }
//             $newWords .= ');' . PHP_EOL;
//             @file_put_contents(
//                     Utils::mergePath(ROOT_PATH, 'libs', 'filterWords',
//                             'contentWords.php'), $newWords);
//             echo "OK\n\n";
//         }
    }

    /**
     * 测试任务
     */
    private function testTask ()
    {}
}
