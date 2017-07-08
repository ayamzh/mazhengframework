<?php
namespace common;
use framework\view;
use framework\core\Context;
use framework\driver\mysql;
use framework\driver\redis;
use framework\exception\NewException;

class Utils
{

    /**
     * 获取当前的准确时间
     *
     * @return float
     */
    public static function getMicrotime ()
    {
        list ($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * 获取当前登录角色ID（可判断是否玩家请求or后台请求）
     *
     * @return int
     */
    public static function getSessionUid ()
    {
        // return (int)(DEBUG_MODE ? $_COOKIE['PHPSESSID'] :
        // @$_COOKIE['PHPSESSID']);
        return (int) (empty($_COOKIE['PHPSESSID']) ? 0 : $_COOKIE['PHPSESSID']);
    }

    /**
     * 合并路径
     *
     * @return string
     */
    public static function mergePath ()
    {
        return implode(DIRECTORY_SEPARATOR, func_get_args());
    }

    /**
     * 从数组中获取一个随机成员
     *
     * @param array $items
     * @return mixed
     */
    public static function getRandomItem ($items)
    {
        return $items[array_rand($items)];
    }

    /**
     * 整理数组键名
     *
     * @param array $arr
     * @param string $key
     * @return array
     */
    public static function refactorArr ($arr, $key)
    {
        $rArr = array();

        if (! empty($arr))
            foreach ($arr as $value)
            {
                $rArr[$value->$key] = $value;
            }

        return $rArr;
    }

    /**
     * 计算概率(保留三位有效数字)
     *
     * @param float $dot
     * @return bool
     */
    public static function countProbability ($dot = 0)
    {
        $dot = round($dot, 3);

        if ($dot <= 0)
            return false;

        $dot = min($dot, 100);

        $one = self::dotRand(0, 100, 3);

        return ($one <= $dot);
    }

    /**
     * 随机小数
     *
     * @param float $min 下限值
     * @param float $max 上限值
     * @param int $decimal 小数位数
     */
    public static function dotRand ($min, $max, $decimal = 0)
    {
        $multiple = pow(10, $decimal);
        $result = round(
                rand(floor($min * $multiple), floor($max * $multiple)) /
                         $multiple, $decimal);
        return $result;
    }

    /**
     * 按权重获取类型
     *
     * @param array $weights [type => weight]
     * @return int
     */
    public static function getWeightItem ($weights)
    {
        $randValue = rand(1, array_sum($weights));
        $limitValue = 0;

        foreach ($weights as $type => $weight)
        {
            $limitValue += $weight;

            if ($randValue <= $limitValue)
            {
                return $type;
            }
        }

        return 0;
    }

    /**
     * 计算字符串显示长度（中文2，英文数字按1来计算）
     *
     * @param string $str
     * @return int
     */
    public static function viewLength ($str)
    {
        return mb_strwidth($str, DEFAULT_CHARSET);
    }

    /**
     * 断言
     * NOTE 由于严格求值特性，各参数都求值，所以后面的参数不能依赖$assertion的事或否的情况，
     * 就像不应该在IF或ELSE中声明变量。
     * @param bool $assertion
     * @param int $errCode
     * @param string $errMsg
     * @param array $errData
     * @throws framework\exception\NewException
     */
    public static function assert ($assertion, $errCode, $errMsg = null,
            $errData = array())
    {

        if (! $assertion)
        {
            throw new NewException($errCode, $errMsg, $errData);
        }
    }

    /**
     * cache是否存在
     *
     * @param XXX $cacheVal
     * @return bool
     */
    public static function cacheExists ($cacheVal)
    {
        return (false !== $cacheVal);
    }

    /**
     * 构造缓存key：变长参数
     *
     * @param int|string $arg1
     * @param int|string ...
     * @return string
     */
    public static function cacheKeyList ()
    {
        $args = func_get_args();
        return implode(KEY_SEPARATOR, $args);
    }

    /**
     * 构造缓存key：向量or映射
     *
     * @param array(int|string) $keys
     * @param bool $map 是否映射
     * @return string
     */
    public static function cacheKeyArray ($keys, $map = false)
    {
        if ($map)
        {
            $tmp = array();
            foreach ($keys as $k => $v)
            {
                $tmp[] = $k;
                $tmp[] = $v;
            }
            $keys = $tmp;
        }

        return implode(KEY_SEPARATOR, $keys);
    }

    /**
     * 取得缓存处理器，Service 专用（战斗除外）
     *
     * @param bool $serialize
     *            是否开启自动序列化
     * @param int $db
     *            选用的DB编号
     * @return \framework\driver\redis\Helper
     */
    public static function cacheHelper ($serialize = true, $db = REDIS_INDEX_CACHE_SERVICE)
    {
        static $inited = array();
        if (! $serialize)
        {
            if (empty($inited[- $db]))
            {
                $cacheConfig = new redis\Config(REDIS_HOST, REDIS_PORT, $db, $serialize);
                redis\Manager::addConfig(- $db, $cacheConfig);
                $inited[- $db] = true;
            }
            return new redis\Helper(- $db);
        }
        else
        {
            return new redis\Helper($db);
        }
    }

    /**
     * 使用过滤词表检查字符串是否合法
     *
     * @param string $string
     * @param boolean $isName
     * @return boolean
     */
    public static function filterString ($string, $isName = true)
    {
        $filterFile = self::mergePath(ROOT_PATH, 'libs', 'filterWords',
                empty($isName) ? 'contentWords.php' : 'nameWords.php');

        if (is_readable($filterFile))
        {
            $filterWordsArray = require ($filterFile);

            if (! empty($filterWordsArray))
            {

                foreach ($filterWordsArray as $filterReg)
                {
                    if (preg_match($filterReg, $string))
                    {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 解析信息字符串(数字,数字...;数字,数字...)
     *
     * @param string $string
     * @return array
     */
    public static function parseInfoStr ($string)
    {
        return (trim($string) == '') ? array() : array_map(
                function  ($piece)
                {
                    return array_map('intval', explode(',', $piece));
                }, array_filter(explode(';', $string)));
    }

    // @return array(key => value)
    public static function parseMap ($string)
    {
        $string = trim($string, "\t \n,\r");
        $map = array();
        if ($string)
        {
            $func = function  ($pair) use( &$map)
            {
                list ($k, $v) = explode(':', $pair);
                $map[$k] = $v;
                return true;
            };
            array_map($func, explode(',', $string));
        }
        return $map;
    }
    // @param string $propSep 属性分隔符（对象or内部数组间用,）
    // @return array(Object|array)
    public static function parseObjects ($string, $desp = array(), $propSep = ':')
    {
        $string = trim($string, "\t \n,\r");
        $objs = array();
        if ($string)
        {
            $func = function  ($obj) use( &$objs, $desp, $propSep)
            {
                $vals = explode($propSep, $obj);
                $objs[] = ($desp ? (object) array_combine($desp, $vals) : $vals);
                return true;
            };
            array_map($func, explode(',', $string));
        }
        return $objs;
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    public static function getClientIP ()
    {
        if (isset($_SERVER))
        {
            $realip = (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '');
        }
        else
        {
            $realip = getenv("REMOTE_ADDR");
        }

        return addslashes($realip);
    }

    /**
     * 判断IP是否在列表中
     *
     * @param string $ip
     * @param array $ipList
     * @return bool
     */
    public static function inIpList ($ip, $ipList)
    {
        $bRange = implode('.', array_slice(explode('.', $ip), 0, 2)) . '.*.*';
        $cRange = implode('.', array_slice(explode('.', $ip), 0, 3)) . '.*';

        if (in_array($ip, $ipList) || in_array($bRange, $ipList) ||
                 in_array($cRange, $ipList))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 格式化字符串
     *
     * @param string $string
     * @param array $params
     * @return string
     */
    public static function formatStr ($string, $params)
    {
        return preg_replace(
                array_map(
                        function  ($key)
                        {
                            return '/\{' . $key . '\}/';
                        }, array_keys($params)), array_values($params), $string);
    }

    /**
     * 初始化配置
     */
    public static function initConfig ()
    {
        // 调试模式设置
        ini_set('display_errors', DEBUG_MODE ? 'On' : 'Off');
        DEBUG_MODE and ini_set('error_reporting', E_ALL);
//         ini_set('error_reporting', E_ALL || ~E_STRICT);

        // 时区设置
        date_default_timezone_set(TIME_ZONE);

        // 配置session
        // ini_set('session.save_handler', 'redis');
        // ini_set("session.gc_maxlifetime","36000");
        // ini_set('session.save_path',
        // 'tcp://'.SESSION_REDIS_HOST.':'.SESSION_REDIS_PORT);
        // session_start();

        // 当前时间和日期
        $GLOBALS['NOW_TIME'] = time();
        $GLOBALS['NOW_DAY']  = date('Y-m-d', $GLOBALS['NOW_TIME']);
        $GLOBALS['NOW_DATE'] = date('Y-m-d H:i:s', $GLOBALS['NOW_TIME']);

        // 初始化模板配置
        view\TemplateView::setConfig(TEMPLATE_TEMPLATE_PATH,
                TEMPLATE_COMPILE_PATH);

        // 初始化 Redis 配置
        foreach ($GLOBALS['REDIS'] as $index => $v)
        {
            // $serialize = ($i != REDIS_INDEX_MAP);
            $cacheConfig = new redis\Config($v['host'], $v['port'], $v['db'], $v['serialize']);
            redis\Manager::addConfig($index, $cacheConfig);
        }

        // 初始化数据库配置
        foreach ($GLOBALS['DB'] as $lib => $v)
        {
            foreach ($v as $type => $db)
            {
                if ($type == 'slave')
                {
                    $hosts = explode(';', $db['host']);
                    foreach ($hosts as $host)
                    {
                        $pdoConfig = new mysql\Config($type, $lib, $host, $db['port'], $db['user'], $db['password'], $db['database']);
                        mysql\Manager::addConfig($pdoConfig);
                    }
                }
                else
                {
                    $pdoConfig = new mysql\Config($type, $lib, $db['host'], $db['port'], $db['user'], $db['password'], $db['database']);
                    mysql\Manager::addConfig($pdoConfig);
                }
            }
        }
    }

    /**
     * 异常处理回调函数
     *
     * @param \Exception $exception
     */
    public static function exceptionHandler (\Exception $exception)
    {
        $ex = Context::formatException($exception);
        $data = (isset($ex['data']) ? $ex['data'] : array());
        $exceptionView = new GameView(
                ($exception instanceof NewException) ? $exception->getCode() : OP_RET_FAIL,
                array(
                        'exception' => $ex,
                        'eData' => $data
                ));
        $exceptionView->display();
    }

    /**
     * 分页
     *
     * @param int $count
     *            项目数
     * @param int $pageSize
     *            页尺寸
     * @param int $pageNo
     *            当前页号
     * @param int $start
     *            开始项目号，反馈
     *
     * @return array(total, perpage, now)
     */
    public static function pageInfo ($count, &$pageSize, $pageNo, &$start)
    {
        $pageSize = max(1, $pageSize);
        $pageNum = max(1, ceil($count / $pageSize));
        $pageNo = max(1, min($pageNum, $pageNo));

        $pageInfo = array(
                'total' => (int) $count,
                'perpage' => (int) $pageSize,
                'now' => (int) $pageNo,
                'max' => (int) $pageNum
        );
        $start = ($pageNo - 1) * $pageSize;
        return $pageInfo;
    }

    /**
     * 取当天某时刻时间戳
     *
     * @param string $clock 时间格式串
     * @return int
     */
    public static function todayTS ($clock = '00:00:00')
    {
        list ($today, $time) = explode(' ', $GLOBALS['NOW_DATE']);
        return strtotime($today . ' ' . $clock);
    }

    /**
     * 取今天过去的秒数
     *
     * @return int
     */
    public static function todaySeconds ()
    {
        list ($today, $time) = explode(' ', $GLOBALS['NOW_DATE']);
        return $GLOBALS['NOW_TIME'] - strtotime($today);
    }

    /**
     * 是否到了指定的时间（无日期部分），[$clockStart, $clockEnd) 左闭右开
     *
     * @param string $clockStart
     *            时间格式串
     * @param string $clockEnd
     *            时间格式串
     * @return bool
     */
    public static function reachClock ($clockStart, $clockEnd = null)
    {
        list ($today, $time) = explode(' ', $GLOBALS['NOW_DATE']);
        if ($time < $clockStart)
        {
            return false;
        }
        return (is_null($clockEnd) or $time < $clockEnd);
    }

    /**
     * 开服天数
     *
     * @return int
     */
    public static function getStartDays ()
    {
        $startTime = strtotime(START_TIME);
        return ceil(($GLOBALS['NOW_TIME'] - $startTime) / 86400);
    }

    /**
     * 锁定
     * @param int $userId 用户ID
     * @param boolean $concurrency 是否是并发操作
     */
    public static function lock ($userId, $concurrency = false)
    {
        if ($concurrency)
        {
            $key = self::cacheKeyList(0, $_REQUEST['op']);
        }
        else
        {
            $key = self::cacheKeyList($userId, $_REQUEST['op']);
        }

        $val = self::cacheHelper(false)->increase($key);

        if ($val > 1)
        {
            $op = $_REQUEST['op'];
            $act = $_REQUEST['act'];
            // 日期 userId op act
            error_log(
                    "{$GLOBALS['NOW_TIME']}\t{$userId}\t{$op}\t{$act}\n",
                    3,
                    HACK_LOG_PATH . 'hack-' . date('Ymd', $GLOBALS['NOW_TIME']) .
                             '.log');

            throw new NewException(NewException::CODE_ERROR_HACK_REFRESH);
        }
        else
        {
            return true;
        }
    }

    /**
     * 解锁
     * @param int $userId 用户ID
     * @param boolean $concurrency
     */
    public static function unlock ($userId, $concurrency = false)
    {
        if ($concurrency)
        {
            $key = self::cacheKeyList(0, $_REQUEST['op']);
        }
        else
        {
            $key = self::cacheKeyList($userId, $_REQUEST['op']);
        }
        self::cacheHelper(false)->delete($key);
    }

    /**
     * 设置登陆验证KEY
     *
     * @param int $passportId
     * @param string $key
     */
    public static function setLoginKey ($passportId, $key)
    {
        self::cacheHelper(false, REDIS_INDEX_CACHE_CHECK)->set(self::cacheKeyList(LOGIN_KEY_PRE, $passportId), $key, LOGIN_KEY_EXPIRE);
    }

    /**
     * 生成登陆验证KEY
     *
     * @param int $passportId
     * @return string
     */
    public static function makeLoginKey ($passportId)
    {
        return md5(SERVER_ID . KEY_SEPARATOR . $passportId . KEY_SEPARATOR . $GLOBALS['NOW_TIME']);
    }

    /**
     * 登陆KEY验证
     *
     * @param int $passportId
     * @param string $key
     */
    public static function loginCheck ($passportId, $key)
    {
        $cacheKey = self::cacheKeyList(LOGIN_KEY_PRE, $passportId);
        $cacheHelper = self::cacheHelper(false, REDIS_INDEX_CACHE_CHECK);
        $loginKey = $cacheHelper->get($cacheKey);
        self::assert($loginKey && $loginKey == $key, NewException::CODE_ERROR_LOGIN_KEY);
        if (Utils::countProbability(30))
        {
            self::setLoginKey($passportId, $loginKey);
        }
    }

    /**
     * 判断是否在时间范围内
     * @param int $startTime
     * @param int $endTime
     * @param int $now
     * @return bool
     */
    public static function inTime($startTime, $endTime, $now = null)
    {
        if (is_null($now))
        {
            $now = $GLOBALS['NOW_TIME'];
        }

        return ($now >= $startTime && $now <= $endTime);
    }

    public static function request($url, $params, $method = 'post')
    {
        $Curl = curl_init(); // 初始化curl

        if ('get' == $method)
        {
            foreach ($params as $k => $v)
            {
                if (isset($arg))
                {
                    $arg .= "&$k=$v";
                }
                else
                {
                    $arg = "$k=$v";
                }

            }

            // 以GET方式发送请求
            curl_setopt($Curl, CURLOPT_URL, "$url?$arg");
        }
        else
        { // 以POST方式发送请求
            curl_setopt($Curl, CURLOPT_URL, $url);
            curl_setopt($Curl, CURLOPT_POST, 1); // post提交方式
            curl_setopt($Curl, CURLOPT_POSTFIELDS, $params); // 设置传送的参数
        }

        curl_setopt($Curl, CURLOPT_HEADER, false); // 设置header
        curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true); // 要求结果为字符串且输出到屏幕上
        curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 5); // 设置等待时间

        $Res = curl_exec($Curl); // 运行curl
        $Err = curl_error($Curl);

        if (false === $Res || ! empty($Err))
        {
            $Errno = curl_errno($Curl);
            $Info = curl_getinfo($Curl);
            curl_close($Curl);

            return array(
                'result' => false,
                'errno' => $Errno,
                'msg' => $Err,
                'info' => $Info
            );
        }
        curl_close($Curl); // 关闭curl

        return array(
            'result' => true,
            'msg' => $Res
        );
    }
}
