<?php
namespace ctrl;
use framework\view;
use framework\exception\NewException;
use common;
use common\Utils;
use common\ServiceLocator;
use common\GameView;

/**
 * 测试控制器
 * web方式：?web=1&PPID=<PPID>...
 * cmd方式：cmd=1 UID=<UID>...
 */
class TestCtrl extends UserCtrlBase
{

    /**
     * 命令行用法：
     * php cron/cmd.php Test.systemMail title=周末登陆送活力值！
     * message='　　封测期间周末有大礼！8月11日、8月12日两天，玩家均可收到官网赠送的20点活力值奖励！<br/>　　满活力的玩家等活力消耗后再领，不然到超出上限部分会消失。'
     * attach='1,3,1,20'
     *
     * php cron/cmd.php Test.systemNotify msg='封测期间周末有大礼！8月11日、8月12日两天，玩家均
     * 可收到官网赠送的20点活力值奖励！满活力的玩家等活力消耗后再领，不然到超出上限部分会消失。'
     */
    public function __construct ()
    {
        if (isset($_SERVER['argv']))
        {
            return;
        }
//         Utils::assert(DEBUG_MODE, 1, 'notAllow');
//         Utils::assert(Utils::inIpList(Utils::getClientIP(), $GLOBALS['SUPER_IPS']), 1, 'notAllow');
    }

    private function isWeb ()
    {
        return (bool) $this->getInteger('web');
    }

    private function isCmd ()
    {
        return (bool) $this->getInteger('cmd');
    }

    // 添加测试数据
    public function adddata ()
    {
        $testSrv = ServiceLocator::getTestService();
        $isok = $testSrv->adddata();
    }

    // 数据库工具，查询
    public function main ()
    {
        $key = $this->getString('key');

        // 查数据
        if ($key)
        {
            $testSrv = ServiceLocator::getTestService();
            $data = $testSrv->get($key);
            return new view\TemplateView('Index.main.debug.html',
                    array(
                            'key' => $key,
                            'data' => var_export($data, true)
                    ));
        }

        return new view\TemplateView('Index.main.debug.html');
    }

    // 数据库工具，更新
    public function update ()
    {
        $key = $this->getString('key');

        $data = $this->getString('modif_str');

        // 改数据
        if ($key)
        {
            $testSrv = ServiceLocator::getTestService();
            $testSrv->update($key, $data);
            return new view\TemplateView('Index.main.debug.html',
                    array(
                            'key' => $key,
                            'data' => $data
                    ));
        }
        else
        {
            return new view\TemplateView('Index.main.debug.html');
        }
    }

    // 更新数据表
    public function addxls ()
    {
        require_once common\Utils::mergePath(ROOT_PATH, 'libs', 'xls',
                'reader.php');

        if ($_FILES["file"]["error"] > 0)
        {
            echo "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        else
        {
            echo "Upload: " . $_FILES["file"]["name"] . "<br />";
            echo "Type: " . $_FILES["file"]["type"] . "<br />";
            echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
            echo "Stored in: " . $_FILES["file"]["tmp_name"] . '<br />';
        }

        move_uploaded_file($_FILES["file"]["tmp_name"],
                'upload/' . $_FILES["file"]["name"]);

        $data = new \Spreadsheet_Excel_Reader();
        // Set output Encoding.
        $data->setOutputEncoding('utf8');
        $data->read('upload/' . $_FILES["file"]["name"]);
        // $data->read($_FILES["file"]["name"]);
        error_reporting(E_ALL ^ E_NOTICE);

        $array = array();
        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i ++)
        {
            for ($j = 1; $j <= $data->sheets[0]['numCols']; $j ++)
            {
                $array[$i][] = $data->sheets[0]['cells'][$i][$j] . "<br>";
            }
        }

        $testService = ServiceLocator::getTestService();

        // 插入数据
        if ($_POST['data_name'] != '')
        {
            $testService->removeAll($_POST['data_name']);
            foreach ($array as $k => $v)
            {
                $testService->add($_POST['data_name'], $v);
            }
        }
    }

    public function addExp ()
    {
        $userHeroService = ServiceLocator::getUserHeroService();

        $userService = ServiceLocator::getUserService();

        $userId = $this->getSessionUserId();
        $user = $userService->read($userId);
        $userHero = $userHeroService->readByHeroId($user->id, 1);

        $userService->addExp($userHero, 1000);
    }

    /**
     * 取当前角色信息
     *
     * @return entity\User
     */
    private function getCurrUser ()
    {
        if ($this->isWeb())
        {
            $ppId = $this->getInteger('PPID', true, true);
            $userSrv = ServiceLocator::getUserService();
            return $userSrv->getOneByPassport($ppId); // NOTE
                                                      // getOneByPassport没走缓存
        }
        elseif ($this->isCmd())
        {
            $userId = $this->getInteger('UID', true, true);
            $userSrv = ServiceLocator::getUserService();
            return $userSrv->read($userId);
        }
        else
        {
            return $this->getSessionUser();
        }
    }

    /**
     * web输出 or API工具返回
     *
     * @param array $keys
     * @param array $objs
     * @param string $label
     */
    private function show ($keys, $objs, $label)
    {
        if ($this->isWeb())
        {
            echo '<h3>', $label, '</h3>', '<br/>';
            echo '<table border=1>';
            echo '<tr>';
            foreach ($keys as $key)
            {
                echo '<th>', $key, '</th>';
            }
            echo '</tr>';
            foreach ($objs as $obj)
            {
                echo '<tr>';
                foreach ($obj as $data)
                {
                    echo '<td>', $data, '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
        else
        {
            $ret = array();
            $ret[] = implode(' : ', $keys);
            foreach ($objs as $obj)
            {
                $ret[] = implode(' : ', $obj);
            }
            return new GameView(OP_RET_SUCCESS,
                    array(
                            $label => $ret
                    ));
        }
    }

    /**
     * command list
     *
     * @return \framework\view\JSONView
     */
    public function cmdList ()
    {
        $ppId = $this->getInteger('PPID', true, true);
        $user = $this->getCurrUser();
        // $userId = $user->id;

        $util = new UtilCtrl();
        $cmds = $util->getOpList(true);
        // $cmds = $cmds[90];
        // return new view\JSONView($cmds);
        header("Content-Type: text/html; charset=utf-8");
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
        echo '<html><head><title> ', $user->name, ' - 测试辅助命令 </title></head><body>';
        $ops = array_merge($cmds[90]['ops'], $cmds[91]['ops']);
        foreach ($ops as $cmd)
        {
            echo '<span style="float:left;">', $cmd['desc'], '</span>';
            echo '<form method="post" target="_blank" action="/">';
            echo '<input type="hidden" name="web" value="1" readonly></input>';
            echo '<input type="hidden" name="act" value="', $cmd['action'], '" readonly></input>';
            echo '<input type="hidden" name="PPID" value="', $ppId, '" readonly></input>';

            echo '<br/>';
            foreach ($cmd['params'] as $param)
            {
                echo '&nbsp;&nbsp;<input type="text" name="', $param['name'], '" value=""></input>';
                echo '&nbsp;&nbsp;<span>', $param['name'], '（', $param['type'], '）', $param['desc'], '</span>';
                echo '<br/>';
            }

            echo '&nbsp;&nbsp;<input type="submit" value="OK"></input>';
            echo '</form>';
        }
        echo '</body><html>';
    }

    /**
     * 服务器错误代码列表
     *
     * @return \framework\view\JSONView
     */
    public function errMsgList ()
    {
        static $sysErr = array(
        /*NewException::CODE_UNINITIALIZE => '系统错误',
         NewException::CODE_ERROR_CALL   => '客户端参数输入错误',
         NewException::CODE_ERROR_ACTION => '元宝不足',
         NewException::CODE_ERROR_PARAMS => '银两不足',*/
        );

        // from config.error.php
        static $platformErr = array(
                ECODE_NOT_OPEN => '服务器未开服',
                ECODE_MAINTENANCE => '服务器维护中',
                ECODE_UNLOGIN => '未登录',
                ECODE_UNINIT => '未初始化',
                ECODE_BAN_LOGIN => '禁止登陆',
                ECODE_GET_BALANCE => '查询余额失败',
                ECODE_USE_GOLD => '使用金币失败',
                ECODE_ADD_GOLD => '添加金币失败',
                ECODE_SUM_GOLD => '获取充值总额失败'
        );

        foreach (NewException::$code2msg as $code => $msg)
        {
            if (isset($sysErr[$code]))
            {
                $msg = $sysErr[$code];
            }
            echo $code, "\t", $msg, "<br/>\n";
        }
        foreach ($platformErr as $code => $msg)
        {
            echo $code, "\t", $msg, "<br/>\n";
        }
        $util = new UtilCtrl();
        $cmds = $util->getOpList(true);
        // unset($cmds[90]);
        foreach ($cmds as $mod)
        {
            foreach ($mod['ops'] as $cmd)
            {
                $op = $cmd['op'];
                foreach ($cmd['rets'] as $ret)
                {
                    $msg = trim($ret['desc']);
                    printf("%d%02d\t%s<br/>\n", $op, $ret['type'], $msg);
                }
            }
        }
    }

    public function mazheng()
    {


        $a = ServiceLocator::getDailyActiveService();
$daily = $a->read(1);
print_R($daily);
echo 'end';
        exit;
        $heros = ServiceLocator::getCampaignHeroService()->getAll();
        $skills = ServiceLocator::getSkillInfoService()->getAll();
        foreach ($skills as $k => $v)
        {
            if (!in_array($v->type,array(1,3,8)))
            {
                $randSkills[$v->job][$k] = $v;
            }
        }
        foreach ($heros as $hero)
        {
            if ($hero->id == 27001) echo "<br><br>";
            if ($hero->level >= 30)
            {
                $keys = array_keys($hero->skillInfo);
                $superSkillId = $keys[0];
                $level = $hero->skillInfo[$keys[0]]['level'];
                $job = $hero->job;
//                 echo $superSkillId . '-'. $level;
//                 echo "<br>";

                $tmpSkills = $randSkills[$job];
                $skillInfo = array($superSkillId => array('level' => $level));
                while (count($skillInfo) < 6)
                {
                    $index = array_rand($tmpSkills);
                    $npcHero->skillInfo[$index] = array(
                        'level' => $level,
                    );
                    $skillInfo[$index] = array('level'=>$level);
                    unset($tmpSkills[$index]);
                }
//                 echo json_encode($skillInfo)."<br>";
                echo json_encode($skillInfo) ."<br>";
            }
        }
        exit;
    }
}
