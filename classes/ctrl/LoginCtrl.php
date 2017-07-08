<?php
namespace ctrl;
use framework\exception\NewException;
use entity;
use common;
use common\ServiceLocator;
use common\Utils;
use common\GameView;
use Api360\Api;
use ApiUc\ApiU;
use ApiBaidu\ApiB;
use ApiLj\ApiLj;

/**
 * 登陆控制逻辑
 */
class LoginCtrl extends UserCtrlBase
{
    public function Login()
    {
        $platform = $this->getString('platform', false);
        $platform = $platform ? $platform : 'self';
        switch ($platform)
        {
            case 'lj':
                $uid          = $this->getString('uid', true);
                $channelid    = $this->getString('channelid', true);
                $token        = $this->getString('token', true);
                $channellabel = $this->getString('channellabel', true);
                $productCode = "p1767";
                $loginAPI = new ApiLj();
                $loginRet = $loginAPI->getUser($uid, $channelid, $token, $productCode, $channellabel);
                if (!empty($loginRet))
                {
                    $passport = array(
                        "passportId"   => $loginRet['id'],
                        "passportName" => $loginRet['name'],
                        "key" => Utils::makeLoginKey($loginRet['id']),
                    );
                }
                break;
            case '360':
                $token  = $this->getString('token', true);
//                 require_once common\Utils::mergePath(ROOT_PATH, 'libs', '360Api', 'Api.php');

                $loginAPI = new Api();
                $loginRet = $loginAPI->getUser($token);
                if (!empty($loginRet))
                {
                    $passport = array(
                        "passportId"   => $loginRet['id'],
                        "passportName" => $loginRet['name'],
                        "key" => Utils::makeLoginKey($loginRet['id']),
                    );
                }

                break;
            case 'uc':
                $sid  = $this->getString('sid', true);
                //                 require_once common\Utils::mergePath(ROOT_PATH, 'libs', '360Api', 'Api.php');

                $loginAPI = new ApiU();
                $loginRet = $loginAPI->getUser($sid);
                if (!empty($loginRet))
                {
                    $passport = array(
                        "passportId"   => $loginRet['accountId'],
                        "passportName" => $loginRet['nickName'],
                        "key" => Utils::makeLoginKey($loginRet['accountId']),
                    );
                }
                break;
                case 'bd':
                    $token  = $this->getString('token', true);
                    //                 require_once common\Utils::mergePath(ROOT_PATH, 'libs', '360Api', 'Api.php');

                    $loginAPI = new ApiB();
                    $loginRet = $loginAPI->getUser($token);
                    if (!empty($loginRet))
                    {
                        $passport = array(
                            "passportId"   => $loginRet['accountId'],
                            "passportName" => $loginRet['nickName'],
                            "key" => Utils::makeLoginKey($loginRet['accountId']),
                        );
                    }
                    break;
            case 'self':
                $passportName = $this->getString('passportName', true);
                $password = $this->getString('password', true);
                require_once common\Utils::mergePath(ROOT_PATH, 'libs', 'kunlun', 'login_api_client.php');

                $loginAPI = new \LoginAPIClient(API_TOKEN, API_LOGIN_SERVER_ADDR);
                $loginRet = $loginAPI->user_innerlogin($passportName, $password);

                if (!empty($loginRet) && isset($loginRet['ret']) && $loginRet['ret'] == 0)
                {
                    $passport = array(
                        "passportId" => $loginRet['passportId'],
                        "passportName" => $passportName,
                        "key" => Utils::makeLoginKey($loginRet['passportId']),
                    );
                }

                break;
        }
        $ip = Utils::getClientIP();
        $now = $GLOBALS['NOW_TIME'];
        if (! empty($passport))
        {
            $userAccountSrv = ServiceLocator::getUserAccountService();
            $userAccount = $userAccountSrv->read($passport['passportId']);
            $users = array();
            if (! is_null($passport['passportName']))
            {
                if (empty($userAccount))
                {
                    $userAccount = new entity\UserAccount();
                    $userAccount->passportId   = $passport['passportId'];
                    $userAccount->passportName = $passport['passportName'];
                    $userAccount->regTime = $now;
                    $userAccount->regIp = $ip;
                    $userAccount->lastLoginIp = $ip;
                    $userAccount->lastLoginTime = $now;
                    $userAccountSrv->add($userAccount);
                }
                else
                {
                    $userAccount->lastLoginIp = $ip;
                    $userAccount->lastLoginTime = $now;
                    $userAccountSrv->update($userAccount, array('lastLoginIp', 'lastLoginTime'));

                    $tmpUser = ServiceLocator::getUserService()->getByPassport($userAccount->passportId);
                    if ($tmpUser) foreach ($tmpUser as $user)
                    {
                        $users[] = array(
                            'id'     => $user->id,
                            'name'   => $user->name,
                            'avatar' => $user->avatar,
                            'level'  => $user->level,
                        );
                        if ($platform == 'uc') $loginAPI->ext($sid, $user);
                        //登陆日志
                        ServiceLocator::getLoginLogService()->addLog($user, entity\LoginLog::TYPE_LOGIN);
                    }
                }
            }

            if (! empty($userAccount) ) //&&$userAccount->status == entity\User::STATUS_LOGIN_OK
            {
                //设置登陆KEY
                Utils::setLoginKey($userAccount->passportId,  $passport['key']);
                return new GameView(OP_RET_SUCCESS,
                    array(
                        'key'        => $passport['key'],
                        'passportId' => $userAccount->passportId,
                        'users'      => $users,
                        'startTime'  => strtotime(START_TIME),
                    ));
            }
        }

        if (!isset($loginRet) || !isset($loginRet['ret']))
        {
            $loginRet = array('ret' => 0);
        }
        throw new NewException($loginRet['ret'], 'loginFailed');
    }

    /**
     * 账号登录
     * @arg string passportName 账号
     * @arg string password 密码
     * @ret 6 密码错误
     * @ret 5 账号不存在
     * @ret 1 网络问题
     */
    public function testLogin ()
    {
        $passportName = $this->getString('passportName', true);
        $password = $this->getString('password', true);
        require_once common\Utils::mergePath(ROOT_PATH, 'libs', 'kunlun', 'login_api_client.php');

        $ip = Utils::getClientIP();
        $now = $GLOBALS['NOW_TIME'];
        $loginAPI = new \LoginAPIClient(API_TOKEN, API_LOGIN_SERVER_ADDR);
        $loginRet = $loginAPI->user_innerlogin($passportName, $password);

        if (!empty($loginRet) && isset($loginRet['ret']) && $loginRet['ret'] == 0)
        {
            $passport = array(
                    "passportId" => $loginRet['passportId'],
                    "passportName" => $passportName,
                    "key" => Utils::makeLoginKey($loginRet['passportId']),
            );
        }

        if (! empty($passport))
        {
            $userAccountSrv = ServiceLocator::getUserAccountService();
            $userAccount = $userAccountSrv->read($passport['passportId']);
            $users = array();

            if (! is_null($passport['passportName']))
            {
                if (empty($userAccount))
                {
                    $userAccount = new entity\UserAccount();
                    $userAccount->passportId = $passport['passportId'];
                    $userAccount->passportName = $passport['passportName'];
                    $userAccount->regTime = $now;
                    $userAccount->regIp = $ip;
                    $userAccount->lastLoginIp = $ip;
                    $userAccount->lastLoginTime = $now;

                    $userAccountSrv->add($userAccount);
                }
                else
                {
                    $userAccount->lastLoginIp = $ip;
                    $userAccount->lastLoginTime = $now;
                    $userAccountSrv->update($userAccount, array('lastLoginIp', 'lastLoginTime'));

                    $tmpUser = ServiceLocator::getUserService()->getByPassport($userAccount->passportId);
                    if ($tmpUser) foreach ($tmpUser as $user)
                    {
                        $users[] = array(
                            'id'     => $user->id,
                            'name'   => $user->name,
                            'avatar' => $user->avatar,
                            'level'  => $user->level,
                        );
                    }
                }
            }

            if (! empty($userAccount) ) //&&$userAccount->status == entity\User::STATUS_LOGIN_OK
            {
                //设置登陆KEY
                Utils::setLoginKey($userAccount->passportId,  $passport['key']);
                return new GameView(OP_RET_SUCCESS,
                        array(
                                'key'        => $passport['key'],
                                'passportId' => $userAccount->passportId,
                                'users'      => $users,
                        ));
            }
        }

        $ret = (isset($loginRet) && $loginRet['ret'] > 0) ? $loginRet['ret'] : 1;
        throw new NewException($loginRet['ret'], 'loginFailed');
    }

//     /**
//      * 用户成功登录
//      *
//      * @return null
//      */
//     public function logined ()
//     {
//         $userId = $this->getSessionUid(); // NOTE 刚注册没角色不能取User实体

//         $userCharService = ServiceLocator::getUserCharService();
//         $userAccount = $userCharService->getAccount($userId);

//         if (! empty($userAccount))
//         {
//             $userIp = Utils::getClientIP();

//             $userSrv = ServiceLocator::getUserService();
//             $user = $userSrv->read($userId);
//             if ($user)
//             {
//                 $userSrv->tmpActivityDaily($user, 11, 1,
//                         array(
//                                 'title' => '【七夕登陆送好礼！】',
//                                 'awards' => '1000信用卷',
//                                 'attaches' => '1,3,3,1000',
//                                 'start' => '2012-08-23',
//                                 'end' => '2012-08-24 23:59:59',
//                                 'count' => 1
//                         ));
//             }

//             if (defined('FIRST_LOGIN_GOLD') and FIRST_LOGIN_GOLD > 0)
//             {
//                 if (date('Ymd') > date('Ymd', $userAccount->lastLoginTime))
//                 {
//                     if (OEM_ID == service\OEMQQService::OEMID)
//                     {
//                         if ($user)
//                         {
//                             $user->coupon += FIRST_LOGIN_GOLD;
//                             $userSrv->update($user, array(
//                                     'coupon'
//                             ));
//                         }
//                     }
//                     else
//                     {
//                         $platformService = ServiceLocator::getPlatformService();
//                         $platformService->addCoin($userAccount,
//                                 FIRST_LOGIN_GOLD, '用户每日首次登陆奖励');
//                     }
//                 }
//             }

//             $userAccount->lastLoginIp = $userIp;
//             $userAccount->lastLoginTime = $GLOBALS['NOW_TIME'];
//             $userAccount->isOnline = 1;

//             $userAccountService = ServiceLocator::getUserAccountService();
//             $userAccountService->update($userAccount,
//                     array(
//                             'lastLoginIp',
//                             'lastLoginTime',
//                             'isOnline'
//                     ));

//             $logService = ServiceLocator::getLogService();
//             $logService->logLogin($userAccount->passportId, $userId,
//                     $GLOBALS['NOW_DATE'], $userIp);

//             $gdssService = ServiceLocator::getGdssService();
//             $gdssService->logLogin($userAccount->passportId,
//                     $userAccount->passportName, $userId, $userIp);

//             $cacheHelper = Utils::cacheHelper(false);
//             $cacheKey = Utils::cacheKeyList('online');
//             $cacheHelper->setAdd($cacheKey, (int) $userId);
//         }

//         return null;
//     }

//     /**
//      * 用户登出
//      *
//      * @return \common\GameView
//      */
//     public function logout ()
//     {
//         // $data = array('op' => 7);
//         // service\PushMessageService::push($data);
//         require_once Utils::mergePath(ROOT_PATH, 'libs', 'kunlun',
//                 'KLSsoClient.class.php');
//         $klClient = new \KLSsoClient();
//         $klClient->logout();

//         // $url = Utils::formatStr(LOGIN_URL, array('HOST' =>SERVER_URL));
//         $url = LOGIN_URL . '?ref=' . SERVER_URL;
//         header("location: {$url}");
//     }

//     /**
//      * 用户成功登出
//      *
//      * @return null
//      */
//     public function logouted ()
//     {
//         $userId = $this->getSessionUid();

//         $userCharService = ServiceLocator::getUserCharService();
//         $userAccount = $userCharService->getAccount($userId);

//         if (! empty($userAccount))
//         {
//             $userAccount->isOnline = 0;

//             $userAccountService = ServiceLocator::getUserAccountService();
//             $userAccountService->update($userAccount, array(
//                     'isOnline'
//             ));

//             $cacheHelper = Utils::cacheHelper(false);
//             $cacheKey = Utils::cacheKeyList('online');
//             $cacheHelper->setRem($cacheKey, (int) $userId);

//             try
//             { // 离开队伍
//                 $battleCtrl = new BattleCtrl();
//                 $battleCtrl->leaveTeamIns($userId);
//                 // 此处不可直接推送消息
//                 $msgs = MessagePool::getMessages();
//                 if ($msgs)
//                 {
//                     foreach ($msgs as $msg)
//                     {
//                         service\PushMessageService::push($msg);
//                     }
//                 }
//             } catch (NewException $e)
//             {}

//             ServiceLocator::getGameToolsService()->recordOnlineTime(
//                     $userAccount->passportId, $userId);
//         }

//         return null;
//     }

}
