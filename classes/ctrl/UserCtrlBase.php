<?php
namespace ctrl;
use framework\core\CtrlBase;

use framework\exception\NewException;
use common;
use common\Utils;
use common\ServiceLocator;

/**
 * 用户控制器基类
 */
class UserCtrlBase extends CtrlBase
{
    /**
     * 用户对象
     * @var \entity\User
     */
    protected static $sessionUser = null;

    /**
     * 用户ID
     * @var int
     */
    protected static $sessionUserId = 0;

    /**
     * 前置过滤器
     *
     * @return bool
     */
    public function beforeExec ($ctrlName = '')
    {
        $clientIp = common\Utils::getClientIP();

        if (common\MessagePool::isUserOp())
        {
            $assertion = ($GLOBALS['NOW_TIME'] >= strtotime(START_TIME) or
                     Utils::inIpList($clientIp, $GLOBALS['SUPER_IPS']));
            Utils::assert($assertion, ECODE_NOT_OPEN, 'notStart',
                    array(
                            'startTime' => START_TIME
                    ));

            $assertion = ($GLOBALS['NOW_TIME'] >= strtotime(MAINTENANCE_ENDLINE) or
                     Utils::inIpList($clientIp, $GLOBALS['SUPER_IPS']));
            Utils::assert($assertion, ECODE_MAINTENANCE, 'maintenance',
                    array(
                            'endTime' => MAINTENANCE_ENDLINE
                    ));

            //分布投放限制
            $act = $this->getString('act', true);

            if (isset($GLOBALS['SYSTEM_OPEN_LIMITED'][$act]))
            {
                $limitLevel = $GLOBALS['SYSTEM_OPEN_LIMITED'][$act];
                $user = $this->getSessionUser();
                Utils::assert($user->level >= $limitLevel, NewException::CODE_ERROR_SYSTEM_NOT_OPEN, null, array($limitLevel));
            }
           if (!in_array($_REQUEST['op'], array(101, 103, 151,3002)))
           {
               $user = $this->getSessionUser();
               $passportId = $this->getString('passportId', true);
               Utils::assert($passportId == $user->passportId, NewException::CODE_ERROR_HACK);
               // 用户登陆验证逻辑
               $loginKey = $this->getString('sk', true);
               Utils::loginCheck($passportId, $loginKey);
           }
        }

        return true;
    }

    /**
     * 清理器
     */
    public function finalExec (){}

    /**
     * 获取当前登录角色ID
     *
     * @return int
     */
    public function getSessionUserId ()
    {
        if (! self::$sessionUserId)
        {
            self::$sessionUserId = $this->getInteger('userId', true, true);
        }

        return self::$sessionUserId;
    }

    /**
     * 获取当前登陆角色
     *
     * @return \entity\User
     */
    public function getSessionUser ()
    {
        if (! self::$sessionUser)
        {
            $userId = $this->getSessionUserId();

            $user = common\ServiceLocator::getUserService()->read($userId);

            if (empty($user))
            {
                throw new NewException(1, 'activate');
            }

//             if ($user->status == entity\User::STATUS_LOGIN_FREEZE)
//             {
//                 throw new NewException(0, 'banuser');
//             }

            self::$sessionUser = $user;
        }

        return self::$sessionUser;
    }

    /**
     * 大区登录验证
     *
     * @return JSONView
     */
    private function doLogin ()
    {
        echo ECODE_UNLOGIN;
        exit();
    }

    /**
     * 验证openId
     */
    public function isOpenId ($openid)
    {
        return (0 == preg_match('/^[0-9a-zA-Z]*$/', $openid)) ? false : true;
    }
}
