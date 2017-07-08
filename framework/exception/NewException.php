<?php
namespace framework\exception;

/**
 * 自定义异常
 *
 */
class NewException extends \Exception
{
    /**
     * 错误代码：框架未初始化
     *
     * @var int
     */
    const CODE_UNINITIALIZE = 101;
    /**
     * 错误代码：错误的调用
     *
     * @var int
     */
    const CODE_ERROR_CALL = 102;
    /**
     * 错误代码：分发动作错误
     *
     * @var int
     */
    const CODE_ERROR_ACTION = 103;
    /**
     * 错误代码：参数错误
     *
     * @var int
     */
    const CODE_ERROR_PARAMS = 104;
    /**
     * 错误代码：类型错误
     * @var int
     */
    const CODE_ERROR_CLASS = 105;
    /**
     * 错误代码：外挂刷请求
     * @var int
     */
    const CODE_ERROR_HACK_REFRESH = 106;
    /**
     * 错误代码：无效配置
     * @var int
     */
    const CODE_ERROR_CONFIG = 107;
    /**
     * 错误代码：登陆验证KEY失效
     * @var int
     */
    const CODE_ERROR_LOGIN_KEY = 109;
//     /**
//      * 错误代码：SESSION验证KEY失效
//      * @var int
//      */
//     const CODE_ERROR_SESSION_KEY = 109;
    /**
     * 错误代码：道具ID不存在
     * @var int
     */
    const CODE_ERROR_WRONG_ITEMID = 110;
    /**
     * 错误代码：玩家无该道具
     * @var int
     */
    const CODE_ERROR_ITEM_NOT_EXIST = 111;
    /**
     * 错误代码：不可使用的道具类型
     * @var int
     */
    const CODE_ERROR_APPLYTYPE_NOTUSE = 112;
    /**
     * 错误代码：hack非法请求
     * @var int
     */
    const CODE_ERROR_HACK = 113;
    /**
     * 错误代码：用户无阵型
     * @var int
     */
    const CODE_ERROR_NO_FORMATION = 114;
    /**
     * 错误代码：用户阵中无武将
     * @var int
     */
    const CODE_ERROR_NO_FORMATINO_HEROES = 115;
    /**
     * 错误代码：用户无武将
     * @var int
     */
    const CODE_ERROR_NO_HEROES = 116;
    /**
     * 错误代码：礼金不足
     * @var int
     */
    const CODE_ERROR_GIFTGOLD_NOT_ENOUGH = 117;
    /**
     * 错误代码：金币不足
     * @var int
     */
    const CODE_ERROR_GOLDCOIN_NOT_ENOUGH = 118;
    /**
     * 错误代码：银两不足
     * @var int
     */
    const CODE_ERROR_SILVER_NOT_ENOUGH = 119;
    /**
     * 错误代码：战斗初始化失败
     * @var int
     */
    const CODE_ERROR_NOT_FIGHT_INIT_FAIL = 120;
    /**
     * 错误代码：将魂数量不足
     * @var int
     */
    const CODE_ERROR_SOUL_NOT_ENOUGH = 121;
    /**
     * 错误代码：添加技能异常
     * @var int
     */
    const CODE_ERROR_ADD_SKILL = 122;
    /**
     * 错误代码：错误的道具效果类型
     * @var int
     */
    const CODE_ERROR_ITEM_EFFECT_TYPE = 123;
    /**
     * 错误代码：错误的道具效果
     */
    const CODE_ERROR_ITEM_EFFECT = 124;
    /**
     * 错误代码：体力不足
     * @var int
     */
    const CODE_ERROR_POWER_NOT_ENOUGH = 125;
    /**
     * 错误代码：探索次数不足
     * @var int
     */
    const CODE_ERROR_EXPLORE_NOT_ENOUGH = 126;
    /**
     * 错误代码：道具数量不足
     * @var int
     */
    const CODE_ERROR_ITEM_NOT_ENOUGH = 127;
    /**
     * 错误代码：系统尚未开放
     * @var int
     */
    const CODE_ERROR_SYSTEM_NOT_OPEN = 128;
    /**
     * 错误代码：数据表错误
     * @var int
     */
    const CODE_ERROR_DATA_ERR = 129;

    /**
     * 错误代码：代码错误
     * @var int
     */
    const CODE_ERROR_BUG = 130;

    /**
     * 扩展信息
     *
     * @var array
     */
    private $data = array();

    /**
     * 标准错误字串
     * @var array(code => msg)
     */
    public static $code2msg = array(
        self::CODE_UNINITIALIZE            => 'Context not initialized',
        self::CODE_ERROR_CALL              => 'invalidCall',
        self::CODE_ERROR_ACTION            => 'opError',
        self::CODE_ERROR_PARAMS            => 'parameterError',
        self::CODE_ERROR_CLASS             => 'invalidClass',
        self::CODE_ERROR_CONFIG            => 'invalidConfig',
        self::CODE_ERROR_HACK_REFRESH      => 'haveARest',
        self::CODE_ERROR_WRONG_ITEMID      => 'wrongItemId',
        self::CODE_ERROR_ITEM_NOT_EXIST    => 'itemNotExist',
        self::CODE_ERROR_APPLYTYPE_NOTUSE  => 'applyTypeNotUse',
        self::CODE_ERROR_HACK              => 'hack',
        self::CODE_ERROR_NO_FORMATION      => 'noFormation',
        self::CODE_ERROR_NO_FORMATINO_HEROES  => 'noFormationHeroes',
        self::CODE_ERROR_NO_HEROES           => 'notHeroes',
        self::CODE_ERROR_GIFTGOLD_NOT_ENOUGH => 'giftNotEnough',
        self::CODE_ERROR_GOLDCOIN_NOT_ENOUGH => 'goldNotEnough',
        self::CODE_ERROR_SILVER_NOT_ENOUGH   => 'currSilverNotEnough',
        self::CODE_ERROR_NOT_FIGHT_INIT_FAIL => 'fightInitFail',
        self::CODE_ERROR_SOUL_NOT_ENOUGH     => 'soulNotEnough',
        self::CODE_ERROR_ADD_SKILL           => 'addSkillErr',
        self::CODE_ERROR_ITEM_EFFECT_TYPE    => 'itemEffectTypeErr',
        self::CODE_ERROR_ITEM_EFFECT         => 'itemEffectErr',
        self::CODE_ERROR_POWER_NOT_ENOUGH    => 'powerNotEnough',
        self::CODE_ERROR_EXPLORE_NOT_ENOUGH  => 'exploreNotEnough',
        self::CODE_ERROR_ITEM_NOT_ENOUGH     => 'itemNotEnough',
        self::CODE_ERROR_SYSTEM_NOT_OPEN     => 'systemNotOpen',
        self::CODE_ERROR_DATA_ERR            => 'tableErr',
        self::CODE_ERROR_BUG                 => 'bug',
    );

    /**
     * 初始化异常
     *
     * @param int $code
     * @param string $message
     * @param array $data
     */
    public function __construct($code, $message = null, $data = array())
    {
        $this->data = $data;
        (!$message and $code > 100) and ($message = self::$code2msg[$code]);
        parent::__construct($message, $code);
    }

    /**
     * 获取扩展信息
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
