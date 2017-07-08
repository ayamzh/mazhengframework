<?php
namespace ctrl;

use common\ServiceLocator;
use common\GameView;
use common\Utils;
use framework\exception\NewException;

/**
 * DEBUG控制器
 */
class DebugCtrl extends UserCtrlBase
{
    public function __construct()
    {
//         Utils::assert(DEBUG_MODE, NewException::CODE_ERROR_HACK,'debugOff');
    }

    /**
     * 添加道具
     * @arg int $itemId 道具ID
     * @arg int $num    道具数量
     * @return GameView
     */
    public function addItem()
    {
        $user   = $this->getSessionUser();
        $itemId = $this->getInteger('itemId', true, true);
        $num    = $this->getInteger('num', true, true);

        $args = new \stdClass();
        $args->num = $num;

        ServiceLocator::getUserItemService()->create($user, $itemId, $args);
        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 添加道具
     * @arg int $equipId 装备ID
     * @arg int $num     装备数量
     * @return GameView
     */
    public function addEquip()
    {
        $user    = $this->getSessionUser();
        $equipId = $this->getInteger('equipId', true, true);
        $num     = $this->getInteger('num', true, true);

        for ($i=0; $i<$num; $i++)
        {
            ServiceLocator::getUserEquipService()->create($user, $equipId);
        }

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 添加英雄
     * @arg int $heroId 英雄ID
     * @return GameView
     */
    public function addHero()
    {
        $user   = $this->getSessionUser();
        $heroId = $this->getInteger('heroId', true, true);

        ServiceLocator::getUserHeroService()->addHero($user->id, $heroId);

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 添加将魂
     * @arg int $heroId 英雄ID
     * @arg int $num    数量
     * @return GameView
     */
    public function addHeroSoul()
    {
        $user   = $this->getSessionUser();
        $heroId = $this->getInteger('heroId', true, true);
        $num    = $this->getInteger('num', true, true);

        ServiceLocator::getHeroSoulService()->create($user->id, $heroId, $num);

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 修改用户信息
     * @arg int $prop    属性名
     * @arg int $value   值
     * @return GameView
     */
    public function editUser()
    {
        $user   = $this->getSessionUser();
        $prop   = $this->getString('prop', true);
        $value  = $this->getInteger('value', true, true);

        $user->$prop = $value;

        ServiceLocator::getUserService()->update($user, array($prop));

        return new GameView(OP_RET_SUCCESS);
    }

    /**
     * 增加金币
     * @arg int $gold  设置金币值
     * @return GameView
     */
    public function setGold()
    {
        $user  = $this->getSessionUser();
        $gold  = $this->getInteger('gold', true, true);
        $userAccountSrv = ServiceLocator::getUserAccountService();
        $userAccount = $userAccountSrv->read($user->passportId);
        $userAccount->goldCoin = $gold;
        $userAccountSrv->update($userAccount, array('goldCoin'));

        return new GameView(OP_RET_SUCCESS);

    }

}
