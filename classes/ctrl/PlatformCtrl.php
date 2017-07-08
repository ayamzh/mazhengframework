<?php
namespace ctrl;

use common\Utils;

use common\ServiceLocator;

use framework\view;

use common;
use entity;

class PlatformCtrl extends UserCtrlBase
{
    /**
     * 检查Token
     *
     * @return bool
     */
    public function beforeExec()
    {
        $clientIp = Utils::getClientIP();

        if (!Utils::inIpList($clientIp, $GLOBALS['PLATFORM_IPS']))
        {
            header("Status: 404 Not Found");
            return false;
        }

        return true;
    }

    /**
     * 返回错误信息
     *
     * @param int $errCode
     * @param string $errMsg
     * @return \framework\view\JSONView
     */
    private function hasError($errCode, $errMsg)
    {
        return new view\JSONView(array(
            'retcode' => $errCode,
            'retmsg' => $errMsg
        ));
    }

    /**
     * 返回结果
     *
     * @param array $result
     * @return \framework\view\JSONView
     */
    private function hasResult($result=array())
    {
        $result['retcode'] = 0;
        $result['retmsg'] = 'OK';

        return new view\JSONView($result);
    }

    /**
     * 获取角色信息
     *
     * @return \framework\view\JSONView
     */
    public function getCharInfo()
    {
        $passportId = $this->getInteger('uid', true);
        $passportName = $this->getString('uname');

        $userAccountService = common\ServiceLocator::getUserAccountService();

        if (!empty($passportId))
        {
            $userAccount = $userAccountService->getByPassportId($passportId);
        }
        elseif (!empty($passportName))
        {
            $userAccount = $userAccountService->getByPassportName($passportName);
        }

        if (empty($userAccount) || empty($userAccount->isInited))
        {
            return $this->hasError(611, 'userNotFound');
        }

        if ($userAccount->status == entity\UserAccount::STATUS_BAN_LOGIN)
        {
            return $this->hasError(612, 'userBanLogin');
        }

        $charInfo = array();

        $userService = common\ServiceLocator::getUserService();
        $users = $userService->getAllByPassport($passportId);

        foreach ($users as $user)
        {
            $charInfo[] = array(
                'cid' => $user->id,
                'cname' => $user->name,
                'inittime' => date('Y-m-d H:i:s', $userAccount->regTime),
                'lastlogin' => date('Y-m-d H:i:s', $userAccount->lastLoginTime),
                'ext' => array()
            );
        }

        return $this->hasResult(array('charInfo' => $charInfo));
    }

    /**
     * 创建角色
     *
     * @return \framework\view\JSONView
     */
    public function createChar()
    {
        $passportId = $this->getInteger('uid', true);
        $passportName = $this->getString('uname');
        $userName = $this->getString('cname');
        $extendInfo = json_decode($this->getString('ext'));

        $userIp = isset($extendInfo['userIp']) ? strval($extendInfo['userIp']) : $_SERVER['REMOTE_ADDR'];
        $avatar = isset($extendInfo['avatar']) ? intval($extendInfo['avatar']) : entity\User::INIT_AVATAR;

        $isValid = common\Utils::filterString($userName);

        if (empty($isValid))
        {
            return $this->hasError(621, 'charNameInvalid');
        }

        $userService = common\ServiceLocator::getUserService();
        $userNum = $userService->countByName($userName);

        if (!empty($userNum))
        {
            return $this->hasError(622, 'charNameExist');
        }

        $user = $userService->getOneByPassport($passportId);

        if (!empty($user))
        {
            return $this->hasError(623, 'charExist');
        }

        $userAccountService = common\ServiceLocator::getUserAccountService();
        $userAccount = $userAccountService->getByPassportId($passportId);

        if (empty($userAccount))
        {
            $userAccount = new entity\UserAccount();
            $userAccount->passportId = $passportId;
            $userAccount->passportName = $passportName;
            $userAccount->regIp = $userIp;
            $userAccount->regTime = $GLOBALS['NOW_TIME'];
            $userAccountService->add($userAccount);
        }

        $userCharService = common\ServiceLocator::getUserCharService();
        $userId = $userCharService->getCharId($passportId);

        $user = new entity\User();
        $user->id = $userId;
        $user->passportId = $passportId;
        $user->name = $userName;
        $user->avatar = $avatar;

        $userService->init($user, $userAccount);

        return $this->hasResult(array('charInfo' => array(
            'cid' => $user->id,
            'cname' => $user->name,
            'inittime' => date('Y-m-d H:i:s', $userAccount->regTime),
            'lastlogin' => date('Y-m-d H:i:s', $userAccount->lastLoginTime),
            'ext' => array()
        )));
    }

    /**
     * 发放奖品
     *
     * @return \framework\view\JSONView
     */
    public function awards()
    {
        $passportId = $this->getInteger('uid', true);
        $userId = $this->getInteger('cid', true);
        $userName = $this->getString('cname');
        $goodsInfo = json_decode(base64_decode($this->getString('gift')),true);

        if (empty($goodsInfo))
        {
            return $this->hasError(631, 'goodsError');
        }

        // TODO:

        return $this->hasResult();
    }

    /**
     * 发送站内信
     *
     * @return \framework\view\JSONView
     */
    public function sendMessage()
    {
        $passportId = $this->getInteger('uid', true);
        $userId = $this->getInteger('cid', true);
        $userName = $this->getString('cname');
        $title = $this->getString('title');
        $content = $this->getString('message');

        if (empty($title))
        {
            return $this->hasError(642, 'errorTitle');
        }

        if (empty($content))
        {
            return $this->hasError(644, 'errorContent');
        }

        // TODO:

        return $this->hasResult();
    }

    /**
     * 充值通知
     *
     * @return \framework\view\JSONView
     */
    public function notifyVouchMsg()
    {
        $passportId   = $this->getInteger('uid', true);
        $vouchCoins   = $this->getInteger('golds');
        $vouchAmount  = $this->getFloat('amount');
        $remainCoins  = $this->getInteger('blanace', true);
        $vouchDate    = $this->getString('vtime');
        $vouchOrderId = $this->getInteger('orderid', true);

        if (!empty($passportId))
        {
            $userAccountService = common\ServiceLocator::getUserAccountService();
            $userAccount = $userAccountService->getByPassportId($passportId);

            if (!empty($userAccount))
            {
                $userAccount->goldCoin += $vouchCoins;
                $userAccountService->update($userAccount, array('goldCoin'));

                //    PushMessageService::push(
                //    array('op' => OP_USER_REFRESH),
                //    array($user->id)
                //    );
            }

            $logService = common\ServiceLocator::getLogService();
            $logService->logCharge($passportId, $vouchAmount, $vouchCoins, $vouchDate);
        }

        return $this->hasResult();
    }

    /**
     * 腾讯发货回调
     */
    public function qqCallback()
    {
        $goodsId = $this->getString('goodsid', true);
        $nums = $this->getString('nums', true);
        $amt = $this->getString('amt', true);
        $ts = $this->getInteger('ts', true,true);
        $uid = $this->getString('uid', true);
        $cid = $this->getString('cid', true);
        $zoneid = $this->getString('zoneid', true);
        $sig = $this->getString('sig', true);
        $token = $this->getString('token', true);

        $check = md5("{$goodsId}|{$nums}|{$amt}|{$ts}|{$uid}|{$cid}|{$zoneid}|" . TOTKEN_KEY);
        if ($check != $sig) {
            return $this->hasError(-1, 'sig err');
        }

        $result = ServiceLocator::getOEMQQService()->delivery($token, $cid, $goodsId, $nums);

        if ($result === true) {
            return $this->hasResult();
        } else {
            return $this->hasError($result['ret'], $result['msg']);
        }
    }
}
