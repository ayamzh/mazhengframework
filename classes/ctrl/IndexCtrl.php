<?php
namespace ctrl;

// use common\Utils;
// use framework\view;
// use common;

/**
 * 首页控制逻辑
 *
 */
class IndexCtrl extends UserCtrlBase
{
//     /**
//      * 主方法，获取首页
//      *
//      * @return \framework\view\TemplateView|null
//      */
//     public function main()
//     {

//         if (isset($_GET['nowTime'])) {
//             echo $GLOBALS['NOW_TIME'];
//             exit;
//         }

//         if (isset($_GET['clientIp'])) {
//             echo Utils::getClientIP();
//             exit;
//         }

//         if (!defined('CURR_HOST')) define('CURR_HOST', $this->get_app_host());

//         $userId = $this->getSessionUserId();

//         return new view\TemplateView('Index.main.html',array(
//                 'staticUrl' => STATIC_SERVER,
//                 'userId' => $userId,
//                 'version' => CLIENT_VERSION,
//                 'langId' => DEFAULT_LOCALE,
//                 'randomNum' => microtime(true),
//                 ));
//     }

//     //获得所在平台域名
//     function get_app_host()
//     {
//         if (!isset($_SERVER['HTTP_HOST'])) return 'self';
//         $arr = explode('.', $_SERVER['HTTP_HOST']);

//         if( 'kx' == $arr[1] ) return 'kaixin';

//         return $arr[1];
//     }

//     /**
//      * 用户登出
//      *
//      * @return null
//      */
//     public function logout()
//     {
//         require_once common\Utils::mergePath(ROOT_PATH, 'lib', 'kunlun', 'KLSsoClient.class.php');

//         $klClient = new \KLSsoClient();
//         $klClient->logout();

//         header('Location: '.LOGIN_URL.'?ref='.SERVER_URL);
//         return null;
//     }
}
