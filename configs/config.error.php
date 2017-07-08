<?php

/**
 * 错误代码：服务器未开服
 *
 * @var int
 */
define('ECODE_NOT_OPEN', 210);
/**
 * 错误代码：服务器维护中
 *
 * @var int
 */
define('ECODE_MAINTENANCE', 211);
/**
 * 错误代码：未登录
 *
 * @var int
 */
define('ECODE_UNLOGIN', 212);
/**
 * 错误代码：未初始化
 *   NOTE: 客户端依赖这个代码判断是否需要注册新角色，
 *         是硬编码的，修改要通知客户端同步
 *
 * @var int
 */
define('ECODE_UNINIT', 213);
/**
 * 错误代码：禁止登陆
 *
 * @var int
 */
define('ECODE_BAN_LOGIN', 214);

/**
 * 错误代码：查询余额失败
 *
 * @var int
 */
define('ECODE_GET_BALANCE', 215);
/**
 * 错误代码：使用金币失败
 *
 * @var int
 */
define('ECODE_USE_GOLD', 216);
/**
 * 错误代码：添加金币失败
 *
 * @var int
 */
define('ECODE_ADD_GOLD', 217);
/**
 * 错误代码：获取充值总额失败
 *
 * @var int
 */
define('ECODE_SUM_GOLD', 218);
