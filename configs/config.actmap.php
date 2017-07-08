<?php
/**
 * 指令成功返回值
 *
 * @var int
 */
define('OP_RET_SUCCESS', 0);
/**
 * 指令失败返回值
 *
 * @var int
 */
define('OP_RET_FAIL', 1);

/**
 * 推送类型：推送给自己
 *
 * @var int
 */
define('PUSH_TO_SELF', 0);
/**
 * 推送类型：推送给所有人
 *
 * @var int
 */
define('PUSH_TO_ALL', 1);
/**
 * 推送类型：推送给组
 *
 * @var int
 */
define('PUSH_TO_GROUP', 2);
/**
 * 推送类型：推送给多个用户
 *
 * @var int
 */
define('PUSH_TO_UIDS', 3);

/**
 * 用户聊天指令
 *
 * @var int
 */
define('OP_CHAT_MSG', 11);

/**
 * 用户信息推送
 * @var int
 */
define('OP_USER_INFO', 106);

/**
 * 指令映射表
 *
 * @var array
 */
$GLOBALS['ACTION_MAP'] = array(
    0   => 'Routine.doRoutine',              // 定时执行任务
    3   => 'Util.doNothing',                 // PHP初始化
    4   => 'Util.doNothing',                 // PHP关闭
    5   => 'Login.login',                    // 登录
    7   => 'Login.logout',                   // 退出成功
    8   => 'Login.logined',                  // 登录成功
    9   => 'Login.logouted',                 // 退出成功
    999  => 'Util.clearCache',                // 清除constcache
    13  => 'Util.indulge',                   // 防沉迷信息
	//998 => 'Hero.attack',                    //内网用，测试方式

    //用户相关
    101 => 'Login.login',                    // 账号登陆
    103 => 'User.init',                      // 创建角色
    105 => 'User.getUser',                   // 获取角色信息
    107 => 'UserTech.getUserTech',           // 获得用户科技列表
    109 => 'UserTech.upTechStar',            // 科技注资
    111 => 'User.levy',                      // 祭祀
    115 => 'User.resetUserExtend',           // 重置用户扩展信息
    119 => 'User.getUserHeart',              // 获得用户心法信息
    121 => 'User.setUserHeart',              // 点亮用户心法
    123 => 'User.getReward',                 // 领取福利奖励
    125 => 'User.luckWheel',                 // 幸运转盘界面
    127 => 'User.turnWheel',                 // 转动转盘

    135 => 'Rank.main',                      // 排行榜
    137 => 'User.luckyWheel',                // 转盘界面
    139 => 'User.turnWheel',                 // 转动转盘
    141 => 'User.silverTree',                // 摇钱树
    143 => 'User.powerGift',                 // 体力礼包
    145 => 'User.mailList',                  // 玩家邮件列表
    147 => 'User.mailInfo',                  // 标记已读
    149 => 'User.getAnnex',                  // 领取附件
    151 => 'User.recharge',                  // 充值
    153 => 'User.getMemberReward',           // 领取月卡奖励
    155 => 'User.setGuideStep',              // 设置新手引导步骤号
    157 => 'User.getDailyReward',            // 领取日常任务奖励

    159 => 'Achievement.getAchievementInfo', // 成就列表
    161 => 'Achievement.getAchievementReward', // 领取成就

    163 => 'User.getBulletin',                //获取公告
    165 => 'User.getRemind',                  //获取强化提醒
    167 => 'User.firstRecharge',              //首充礼包
    169 => 'User.giftCode',                   //兑换码
    171 => 'Rank.main2',                      // 排行榜
    173 => 'User.getActiveReward',            //活跃度奖励
    175 => 'User.getUserHandBook',            //用户图鉴
    177 => 'User.kofMain',                    //KOF界面
    179 => 'User.signUp',                     //报名武将
    181 => 'User.bet',                        //下注
    183 => 'User.kofReport',                  //观看比武大会战报
    185 => 'Chat.main',                       //聊天界面
    187 => 'Chat.sendMsg',                    //聊天
    191 => 'User.kofBuy',                     //比武会兑换奖励
    193 => 'User.kofShop',                     //比武会商店

    //背包
    201 => 'Bag.itemList',                   // 获取用户道具
    203 => 'Bag.useItem',                    // 使用道具
//      205 => 'Bag.arrange',                  // 整理背包
    207 => 'Bag.mergeList',                  // 道具合成列表
    209 => 'Bag.merge',                      // 道具合成
    211 => 'Bag.sell',                       // 道具出售

    //装备相关
    301 => 'Equip.equipList',                 // 装备界面
    303 => 'Equip.useEquip',                  // 穿脱装备
    305 => 'Equip.sellEquip',                 // 出售装备
    307 => 'Equip.upEquip',                   // 装备强化
    313 => 'Equip.jewelInlay',                // 宝石镶嵌
    315 => 'Equip.jewelList',                 // 宝石列表
    317 => 'Equip.mergeEquip',                // 合成装备
    319 => 'Equip.changeEquip',               // 装备进阶
    321 => 'Equip.weaponList',                // 兵器界面
    323 => 'Equip.weaponUp',                  // 兵器升级

    //战斗相关
//     401 => 'Campaign.getCampaignMapInfo',     // 游戏主界面-推图界面
    403 => 'Campaign.getCampaignInfo',        // 获取战役详细信息
    405 => 'Campaign.attackArmy',             // 挑战战役部队
    407 => 'Campaign.resetAttackNum',         // 重置挑战次数

    409 => 'Elite.getEliteMapInfo',           // 过关斩将界面
    411 => 'Elite.getEliteInfo',              // 获取过关斩将详细信息
    413 => 'Elite.attackArmy',                // 挑战过关斩将部队
    415 => 'Elite.resetAttackNum',            // 重置过关斩将挑战次数

    417 => 'Campaign.getReward',              // 领取首次通关奖励

    //武将相关
    501 => 'Hero.heroList',                   // 武将列表
//     503 => 'Hero.info',                    // 武将详情
    505 => "Hero.formation",                  // 阵型界面
    507 => 'Hero.setFormation',               // 武将布阵
    509 => 'Hero.pub',                        // 酒馆界面
    511 => 'Hero.refresh',                    // 武将刷新
    513 => 'Hero.recruit',                    // 武将招募
    515 => 'Hero.fire',                       // 武将解雇
    517 => 'Hero.soulList',                   // 将魂列表
    519 => 'Hero.upgrade',                    // 武将升星
    521 => 'Hero.soulMerge',                  // 将魂合成
    523 => 'Hero.skillUpgrade',               // 技能升级
    525 => 'Hero.skillSet',                   // 技能设置
    527 => 'Hero.bindHero',                   // 绑定副将
//     529 => 'Hero.unbindHero',              // 解除副将
    531 => 'Hero.heroInfo',                   // 英雄详细信息



    //PVP系统
    601 => 'Arena.main',                      // 竞技场界面
    603 => 'Arena.attack',                    // 竞技场挑战
    605 => 'Arena.awardRank',                 // 竞技场领取排位奖励
    607 => 'Arena.rankMain',                  // 竞技场排行界面
    609 => 'Arena.buyAttackNum',              // 购买竞技场挑战
    611 => 'Arena.buy',                       // 购买
    613 => 'Arena.refresh',                   // 刷新商店

    627 => 'Arena.reportList',                // 竞技场战报列表
    629 => 'Arena.viewReport',                // 观看竞技场战报
    631 => 'Transport.viewWantedReport',      // 观看通缉令战报
    633 => 'Transport.getReports',            // 劫镖战报列表
    635 => 'Transport.viewReport',            // 观看劫镖战报

    637 => 'Arena.viewBrother',               // 膜拜大哥
    639 => 'Transport.plunderMain',           // 劫镖主界面
    641 => 'Transport.drop',                  // 放弃运镖
    643 => 'Transport.getReward',             // 领取运镖奖励

    645 => 'Arena.viewHero',                  // 查看武将

    //PVE系统
    701 => 'Explore.main',                    // 奇遇界面
    703 => 'Explore.explore',                 // 探索操作
    705 => 'Explore.doEvent',                 // 处理事件
    707 => 'Explore.dropEvent',               // 放弃事件
    709 => 'Explore.getReward',               // 奇遇领奖
    711 => 'Huarong.getHuarongInfo',           // 华容道界面
    713 => 'Huarong.resetHuarongInfo',           // 重置华容道
    715 => 'Huarong.attackHuarong',           // 攻击华容道
    717 => 'Huarong.getReward',                 // 华容道领奖
    719 => 'Explore.buyTimes',                // 购买奇遇探索次数
    721 => 'Training.attack',                 // 试炼挑战
    723 => 'Boss.main',                       // 世界BOSS界面
    725 => 'Boss.attack',                     // 攻击世界BOSS
    727 => 'Boss.getReport',                  // 获取战报
    729 => 'Training.trainingInfo',           // 试炼状态信息

    //商城
    801 => 'Shop.main',                       // 商城界面
    803 => 'Shop.buy',                        // 购买
    805 => 'Shop.info',                       // 商品详情

    //好友
    901 => 'Friend.main',                     // 好友列表
    903 => 'Friend.apply',                    // 申请好友
    905 => 'Friend.accept',                   // 处理好友请求
    907 => 'Friend.refresh',                  // 刷新好友
    909 => 'Friend.sendGift',                 // 送体力
    911 => 'Friend.getGift',                  // 领取体力
    913 => 'Friend.pvp',                      // 切磋
    915 => 'Friend.sendMail',                 // 发邮件
    917 => 'Friend.drop',                     // 删除好友
    919 => 'Friend.main',                     // 聊天界面
    921 => 'Friend.sendMsg',                  // 发消息

    1001 => 'Test.adddata',                   //测试数据添加
    1002 => 'Test.main',
    1003 => 'Test.addxls',
    1004 => 'Test.mazheng',                   //测试接口
    1005 => 'Hero.attack',                    //请求战斗
    1006 => 'Test.errMsgList',                //错误列表

    1101 => 'Activity.getActivityInfo',	      //活动列表
    1103 => 'Activity.getReward',             //领取活动奖励

    //DEBUG命令
    2001 => 'Debug.addItem',                  //添加道具
    2003 => 'Debug.addEquip',                 //添加装备
    2005 => 'Debug.addHero',                  //添加武将
    2007 => 'Debug.addHeroSoul',              //添加将魂
    2009 => 'Debug.editUser',                 //编辑角色属性
    2011 => 'Debug.setGold',                  //添加金币
    2013 => 'Debug.resetUserExtend',          //重置角色扩展信息

    3001 => 'Util.flushCache',                //
    3002 => 'User.sendReward',                // 发奖

);

//系统开放限制
$GLOBALS['SYSTEM_OPEN_LIMITED'] = array(
//     'Equip.upEquip'       =>  3,
//     'Equip.jewelInlay'    =>  3,
//     'Equip.jewelList'     =>  3,

//     'User.getUserHeart'   =>  5,
//     'User.getUserHeart'   =>  5,

//     'Hero.upgrade'        =>  6,

//     'Hero.pub'            =>  7,
//     'Hero.refresh'        =>  7,
//     'Hero.recruit'        =>  7,
);
