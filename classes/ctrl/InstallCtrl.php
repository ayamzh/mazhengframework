<?php
namespace ctrl;

use common\Utils;
use common\ServiceLocator;

use framework\driver\constcache;

/**
 * 服务器安装控制器
 *
 */
class InstallCtrl extends SysCtrlBase
{
    /**
     * 错误日志文件路径
     *
     * @var string
     */
    private $logFile = null;

    /**
     * constCacheHelper
     *
     * @var framework\driver\constcache\Helper
     */
    private $cacheHelper = null;

    /**
     * 预处理和检查
     *
     */
    public function __construct()
    {
        if (empty($_SERVER['argv']) || !preg_match('/.*cmd\.php$/', $_SERVER['argv'][0]))
        {
            exit();
        }

        $this->cacheHelper = new constcache\Helper();
    }

    public function setLogPath($name)
    {
        $this->logFile = Utils::mergePath(ROOT_PATH, 'webroot', 'data', $name.'Error.'.date('Ymd').'.txt');
    }

    /**
     * 用于相应单个命名调用请求的魔术方法
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (!method_exists($this, $method))
        {
            return null;
        }

        return call_user_func_array(array($this, $method), array($this->params));
    }

    /**
     * 写入错误日志
     *
     * @param string $content
     */
    private function writeLog($content)
    {
        error_log($content, 3, $this->logFile);
    }

    /**
     * 记录步骤
     *
     * @param string $opName
     */
    public function markStart($opName)
    {
        echo "[ ", $GLOBALS['NOW_DATE'], " ][ ", $opName, " Start ] \n";
    }

    /**
     * 记录步骤
     *
     * @param string $opName
     */
    public function markEnd($opName)
    {
        echo "[ ", $GLOBALS['NOW_DATE'], " ][ ", $opName, " End ] \n";
    }

    /**
     * 安装服务器
     */
    public function install()
    {
        $this->markStart('安装程序');

        try
        {
            //清理缓存
            $this->cacheHelper->flush();

            $nameTxt = Utils::mergePath(DATA_PATH, 'name.txt');
            //初始化竞技场机器人
            $randNames = json_decode(file_get_contents($nameTxt), true);

            $heros = ServiceLocator::getHeroService()->getPubAll();
            $skills = ServiceLocator::getSkillInfoService()->getAll();
            $randSkills = array();
            foreach ($skills as $k => $v)
            {
                if (!in_array($v->type,array(1,3,8)))
                {
                    $randSkills[$v->job][$k] = $v;
                }
            }

            $npcHeroSrv = ServiceLocator::getUserNpcHeroService();
            //清空数据表
            $userArenaSrv = ServiceLocator::getUserArenaService();
            $userArenaSrv->clear();
            $npcHeroSrv =  ServiceLocator::getUserNpcHeroService();
            $npcHeroSrv->clear();
            //机器人配置
            $botConfig = array(
                5    => array('level'=>36, 'quality'=>7, 'force'=>82, 'intelligence'=>82, 'command'=>82, 'attack'=>array(1100, 900), 'defend'=>array(560, 500), 'speed'=>array(560, 500), 'hp'=>array(5900, 5500), 'hit'=>array(110, 100), 'dodge'=>array(110, 100), 'crit'=>array(110, 100), 'toughness'=>array(110, 100), 'skillLevel'=>20),
                50   => array('level'=>34, 'quality'=>6, 'force'=>81, 'intelligence'=>81, 'command'=>81, 'attack'=>array(950, 850), 'defend'=>array(530, 450), 'speed'=>array(530, 450), 'hp'=>array(5500, 5000), 'hit'=>array(108, 90), 'dodge'=>array(108, 90), 'crit'=>array(108, 90), 'toughness'=>array(108, 90), 'skillLevel'=>19),
                100  => array('level'=>32, 'quality'=>6, 'force'=>81, 'intelligence'=>81, 'command'=>81, 'attack'=>array(860, 830), 'defend'=>array(430, 400), 'speed'=>array(430, 400), 'hp'=>array(4500, 4200), 'hit'=>array(85, 75), 'dodge'=>array(85, 75), 'crit'=>array(85, 75), 'toughness'=>array(85, 75), 'skillLevel'=>18),
                200  => array('level'=>30, 'quality'=>5, 'force'=>80, 'intelligence'=>80, 'command'=>80, 'attack'=>array(770, 740), 'defend'=>array(380, 360), 'speed'=>array(380, 360), 'hp'=>array(4100, 3900), 'hit'=>array(75, 70), 'dodge'=>array(75, 70), 'crit'=>array(75, 70), 'toughness'=>array(75, 70), 'skillLevel'=>17),
                300  => array('level'=>28, 'quality'=>5, 'force'=>80, 'intelligence'=>80, 'command'=>80, 'attack'=>array(700, 660), 'defend'=>array(350, 320), 'speed'=>array(350, 320), 'hp'=>array(3700, 3500), 'hit'=>array(67, 64), 'dodge'=>array(67, 64), 'crit'=>array(67, 64), 'toughness'=>array(67, 64), 'skillLevel'=>15),
                400  => array('level'=>26, 'quality'=>4, 'force'=>80, 'intelligence'=>80, 'command'=>80, 'attack'=>array(620, 590), 'defend'=>array(310, 290), 'speed'=>array(310, 290), 'hp'=>array(3200, 3000), 'hit'=>array(60, 55), 'dodge'=>array(60, 55), 'crit'=>array(60, 55), 'toughness'=>array(60, 55), 'skillLevel'=>15),
                500  => array('level'=>24, 'quality'=>4, 'force'=>80, 'intelligence'=>80, 'command'=>80, 'attack'=>array(560, 540), 'defend'=>array(280, 250), 'speed'=>array(280, 250), 'hp'=>array(2900, 2700), 'hit'=>array(54, 50), 'dodge'=>array(54, 50), 'crit'=>array(54, 50), 'toughness'=>array(54, 50), 'skillLevel'=>15),
                1000 => array('level'=>22, 'quality'=>3, 'force'=>79, 'intelligence'=>79, 'command'=>79, 'attack'=>array(470, 455), 'defend'=>array(247, 227), 'speed'=>array(247, 227), 'hp'=>array(2500, 2400), 'hit'=>array(46, 44), 'dodge'=>array(46, 44), 'crit'=>array(46, 44), 'toughness'=>array(46, 44), 'skillLevel'=>12),
                1500 => array('level'=>20, 'quality'=>3, 'force'=>78, 'intelligence'=>78, 'command'=>78, 'attack'=>array(450, 400), 'defend'=>array(220, 210), 'speed'=>array(210, 200), 'hp'=>array(2200, 2000), 'hit'=>array(40, 42), 'dodge'=>array(42, 40), 'crit'=>array(42, 40), 'toughness'=>array(42, 40), 'skillLevel'=>10),
                2000 => array('level'=>18, 'quality'=>2, 'force'=>77, 'intelligence'=>77, 'command'=>77, 'attack'=>array(400, 330), 'defend'=>array(180, 160), 'speed'=>array(175, 160), 'hp'=>array(1800, 1600), 'hit'=>array(32, 30), 'dodge'=>array(32, 30), 'crit'=>array(32, 30), 'toughness'=>array(32, 30), 'skillLevel'=>8),
                2400 => array('level'=>16, 'quality'=>2, 'force'=>76, 'intelligence'=>76, 'command'=>76, 'attack'=>array(330, 300), 'defend'=>array(180, 160), 'speed'=>array(175, 160), 'hp'=>array(1800, 1600), 'hit'=>array(32, 30), 'dodge'=>array(32, 30), 'crit'=>array(32, 30), 'toughness'=>array(32, 30), 'skillLevel'=>5),
            );

            for ($i = -1; $i >= -2400; $i--)
            {
                $rank = abs($i);
                $botCnf = array();
                foreach ($botConfig as $k => $bot)
                {
                    if($rank <= $k)
                    {
                        $botCnf = $bot;
                        break;
                    }
                }

                $tmpHeros = array();
                while (count($tmpHeros) < 3)
                {
                    $quality = $botCnf['quality'];
                    //随机英雄
                    $hero = $heros[$quality][array_rand($heros[$quality])];

                    if (!array_key_exists($hero->id, $tmpHeros))
                    {
                        $tmpHeros[$hero->id] = $hero;
                    }
                }

                $avatar = 0;
                $fightValue = 0;
                $pos = 1;
                foreach ($tmpHeros as $tmpHero)
                {
                    if (!$avatar) $avatar = $tmpHero->avatar;
                    $npcHero = new \entity\UserNpcHero();
                    $npcHero->userId = $i;
                    $npcHero->heroId = $tmpHero->id;
                    $npcHero->name   = $tmpHero->name;
                    $npcHero->avatar = $tmpHero->avatar;
                    $npcHero->weaponId = $tmpHero->weaponId;
                    $npcHero->job      = $tmpHero->job;
                    $npcHero->sex      = $tmpHero->sex;
                    $npcHero->color    = $tmpHero->color;
                    $npcHero->position = $pos++;
                    $npcHero->level    = $botCnf['level'];
                    $npcHero->force    = $botCnf['force'];
                    $npcHero->intelligence = $botCnf['intelligence'];
                    $npcHero->command    = $botCnf['command'];
                    $npcHero->attack     = mt_rand($botCnf['attack'][1], $botCnf['attack'][0]);
                    $npcHero->defend     = mt_rand($botCnf['defend'][1], $botCnf['defend'][0]);
                    $npcHero->speed      = mt_rand($botCnf['speed'][1], $botCnf['speed'][0]);
                    $npcHero->hp         = mt_rand($botCnf['hp'][1], $botCnf['hp'][0]);
                    $npcHero->hit        = mt_rand($botCnf['hit'][1], $botCnf['hit'][0]);
                    $npcHero->dodge      = mt_rand($botCnf['dodge'][1], $botCnf['dodge'][0]);
                    $npcHero->crit       = mt_rand($botCnf['crit'][1], $botCnf['crit'][0]);
                    $npcHero->toughness  = mt_rand($botCnf['toughness'][1], $botCnf['toughness'][0]);
                    $npcHero->skillInfo  = array($tmpHero->skillId => array('level'=>$botCnf['skillLevel'],'pos'=>1));
                    $npcHero->passiveSkill = $tmpHero->passiveSkill;

                    $tmpSkills = $randSkills[$tmpHero->job];
                    $p = 2;
                    while (count($npcHero->skillInfo) < 5)
                    {
                        $index = array_rand($tmpSkills);
                        $npcHero->skillInfo[$index] = array(
                            'level' => $botCnf['skillLevel'],
                            'pos'   => $p++,
                        );
                        unset($tmpSkills[$index]);
                    }

                    $fv = \service\UserHeroService::countFightValue((array)$npcHero);
                    $fightValue += $fv;

                    $npcHeroSrv->add($npcHero);
                }

                $userArena = new \entity\UserArena();
                $userArena->userId = $i;
                $userArena->userName = $randNames['surname'][array_rand($randNames['surname'])] . $randNames['name'][array_rand($randNames['surname'])];
                $userArena->avatar = $avatar;
                $userArena->userLevel = $botCnf['level'];
                $userArena->fightValue = $fightValue;
                $userArena->rank = $rank;
                $userArena->historyTop = $rank;
                $userArenaSrv->add($userArena);
            }
        }
        catch (\Exception $exception)
        {
            $errorMsg = "Error Time: {$GLOBALS['NOW_DATE']}\n";
            $errorMsg .= "{$exception}\n";

            $this->setLogPath(__METHOD__);
            $this->writeLog($errorMsg);
        }

        $this->markEnd('安装程序');
    }

}
