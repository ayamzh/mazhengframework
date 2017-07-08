<?php
/**
 * 项目根路径
 *
 * @var string
 */
define('ROOT_PATH', realpath(__DIR__.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
/**
 * 逻辑代码目录
 *
 * @var string
 */
define('CLASSES_PATH', ROOT_PATH.'classes'.DIRECTORY_SEPARATOR);
/**
 * 配置文件目录
 *
 * @var string
 */
define('CONFIG_PATH', ROOT_PATH.'configs'.DIRECTORY_SEPARATOR);
/**
 * 数据文件目录
 *
 * @var string
 */
define('DATA_PATH', ROOT_PATH.'data'.DIRECTORY_SEPARATOR);
/**
 * 类库文件目录
 *
 * @var string
 */
define('LIBS_PATH', ROOT_PATH.'libs'.DIRECTORY_SEPARATOR);

/**
 * 编译路径
 *
 * @var string
 */
define('TEMPLATE_COMPILE_PATH', ROOT_PATH.'templates/compile');
/**
 * 模板路径
 *
 * @var string
 */
define('TEMPLATE_TEMPLATE_PATH', ROOT_PATH.'templates/template');
/**
 * GDSS日志路径
 *
 * @var string
 */
define('GDSS_LOG_PATH', '/data/syslog/platformlog');
/**
 * 刷请求类外挂日志
 *
 * @var string
 */
define('HACK_LOG_PATH', '/data/avatar/log/');

