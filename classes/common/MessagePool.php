<?php
namespace common;

/**
 * 消息池
 *
 */
class MessagePool
{
    /**
     * 消息
     *
     * @var array
     */
    private static $messages = array();

    /**
     * 是否是用户请求
     *
     * @return boolean
     */
    public static function isUserOp()
    {
        return (isset($_REQUEST['op']) && $_REQUEST['op'] > 100) ? true : false;
    }


    /**
     * 打包[一般数据]消息，自动push
     *
     * @param int          $ret       结果值
     * @param array        $data      数据
     * @param array        $to        接受方
     *
     * @return array    格式化后的数据
     */
    public static function packData($ret, $data, $to = null)
    {
        return self::push(self::packDataNoPush($ret, $data, $to));
    }

    public static function packDataNoPush($ret, $data, $to = null)
    {
        //TODO
        $data['op'] = 100;

        $model = array();

        if (!empty($to))
        {
            $data['to'] = $to;
        }
        elseif (self::isUserOp())
        {
            $data['to'] = array(Utils::getSessionUid());
        }

        if (empty($data['op']) && self::isUserOp())
        {
            $model['op'] = $_REQUEST['op'] + 1;
        }
        else
        {
            $model['op'] = $data['op'];
            unset($data['op']);

        }

        $model['ret']     = $ret;
        $model['time']    = $GLOBALS['NOW_TIME'];
        $model['version'] = CLIENT_VERSION;
        $model['data']    = $data;

        return $model;
    }

    /**
     * 打包[系统频道信息]消息
     *
     * @param string       $message       发言内容
     * @param array        $extendData    扩展信息
     * @param int          $pushType      是否发送到组
     * @param mixed        $pushIds       ID或ID序列
     *
     * @return array    格式化后的数据
     */
    public static function packSysMsg($message, $extendData = array(), $pushType = PUSH_TO_SELF, $pushIds = array())
    {
        if ($pushType == PUSH_TO_SELF)
        {
            $to = array(Utils::getSessionUid());
        }
        elseif ($pushType == PUSH_TO_ALL)
        {
            $to = array('all+');
        }
        elseif ($pushType == PUSH_TO_GROUP)
        {
            $to = array_map(function($groupId)
            {
                return '#+' . $groupId;
            }, (array)$pushIds);
        }
        else
        {
            $to = (array)$pushIds;
        }

        return array(
            'to'       => $to,
            'op'       => OP_CHAT_MSG,
            'ret'      => OP_RET_SUCCESS,
            'type'     => CHAT_CHANNEL_SYSTEM,
            'id'       => 0,
            'name'     => NULL,
            'msg'      => $message,
            'chatdata' => json_encode($extendData)
        );
    }

    /**
     * 打包[世界频道发言]消息
     *
     * @param int          $userId        发言者ID
     * @param string       $userName      发言者姓名
     * @param string       $message       发言内容
     * @param array        $extendData    扩展信息
     *
     * @return array    格式化后的数据
     */
    public static function packWorldMsg($userId, $userName, $message, $extendData = array())
    {
        return array(
            'to'       => array('all+'),
            'op'       => OP_CHAT_MSG,
            'ret'      => OP_RET_SUCCESS,
            'type'     => CHAT_CHANNEL_WORLD,
            'id'       => intval($userId),
            'name'     => $userName,
            'msg'      => $message,
            'chatdata' => json_encode($extendData)
        );
    }

    /**
     * 打包[创建组]消息
     *
     * @param    int    $groupId    组ID
     *
     * @return array    格式化后的数据
     */
    public static function packAddGroup($groupId)
    {
        return self::push(array(
            'phpcmd' => 'groupAdd',
            'id'     => $groupId));
    }

    /**
     * 打包[删除组]消息
     *
     * @param    int    $groupId    组ID
     *
     * @return array    格式化后的数据
     */
    public static function packDelGroup($groupId)
    {
        return self::push(array(
            'phpcmd' => 'groupRemove',
            'id'     => $groupId));
    }

    /**
     * 打包[清空组]消息
     *
     * @param    int    $groupId    组ID
     *
     * @return array    格式化后的数据
     */
    public static function packClearGroup($groupId)
    {
        return self::push(array(
            'phpcmd' => 'groupClear',
            'id'     => $groupId));
    }

    /**
     * 打包[添加组成员]消息
     *
     * @param    int        $groupId        组ID
     * @param    mixed      $userIds        成员ID（单个或数组）
     * @param    boolean    $createGroup    是否创建组
     *
     * @return array    格式化后的数据
     */
    public static function packAddMember($groupId, $userIds, $createGroup = true)
    {
        if ($createGroup)
        {
            self::packAddGroup($groupId);
        }

        return self::push(array(
            'phpcmd'     => 'groupAddMember',
            'id'         => $groupId,
            'charIds'    => array_map('intval', (array)$userIds)
        ));
    }

    /**
     * 打包[移除组成]员消息
     *
     * @param    int        $groupId    组ID
     * @param    mixed      $userIds    成员ID（单个或数组）
     *
     * @return array    格式化后的数据
     */
    public static function packDelMember($groupId, $userIds)
    {
        return self::push(array(
            'phpcmd'     => 'groupRemoveMember',
            'id'         => $groupId,
            'charIds'    => array_map('intval', (array)$userIds)
        ));
    }

    /**
     * 打包[用户禁言]消息
     *
     * @param    mixed    $userIds    用户ID（单个或数组）
     *
     * @return array    格式化后的数据
     */
    public static function packBanChat($userIds)
    {
        return self::push(array(
            'phpcmd'  => 'banChat',
            'charIds' => array_map('intval', (array)$userIds)));
    }

    /**
     * 打包[解除用户禁言]消息
     *
     * @param    mixed    $userIds    用户ID（单个或数组）
     *
     * @return array    格式化后的数据
     */
    public static function packUnbanChat($userIds)
    {
        return self::push(array(
            'phpcmd'  => 'unbanChat',
            'charIds' => array_map('intval', (array)$userIds)));
    }

    /**
     * 打包[T用户]消息
     *
     * @param    mixed    $userIds    用户ID（单个或数组）
     *
     * @return array    格式化后的数据
     */
    public static function packKickout($userIds)
    {
        return self::push(array(
            'phpcmd'  => 'kickout',
            'charIds' => array_map('intval', (array)$userIds)));
    }

    /**
     * 打包设置用户角色名消息
     *
     * @param string $userName    用户角色名
     *
     * @return array    格式化后的数据
     */
    public static function packSetCharName($userName)
    {
        return self::push(array(
            'phpcmd'      => 'sessSetName',
            'charName'    => $userName));
    }

    /**
     * 打包设置用户其他属性消息
     * @param int $country
     * @return array    格式化后的数据
     */
    public static function packSetChatData($country)
    {
        return self::push(array(
            'phpcmd' => 'sessSetChatData',
            // 'data'   => json_encode(array('country' => $country)),));
            'data'   => $country,));
    }

    /**
     * 打包获取在线人数消息
     *
     * @return array    格式化后的数据
     */
    public static function packCheckOnline()
    {
        return self::push(array('phpcmd' => 'getPlayerCount'));
    }

    /**
     * 打包[进入地图]消息
     *
     * @param int   $teamId     战队ID
     * @param array $teamInfo   战队信息（json）
     *   格式：array(battleId, mapId)
     * @param array $playerInfo 玩家信息（json）
     * @param array $aiPlayer   ai玩家数据
     *
     * @return array    格式化后的数据
     */
    public static function packEnterMap($teamId, $teamInfo, $playerInfo, $aiPlayer = null)
    {
        return self::push(array(
            'phpcmd'     => 'sessEnterMap',
            'instanceId' => $teamId,
            'charData'   => json_encode(array('playerInfo' => $playerInfo, 'aiPlayerInfo' => $aiPlayer)),
            'mapData'    => json_encode(array('teamInfo' => $teamInfo))));
    }

    /**
     * 打包[离开地图]消息
     *
     * @param int   $teamId     战队ID
     *
     * @return array    格式化后的数据
     */
    public static function packLeaveMap($teamId)
    {
        return self::push(array(
            'phpcmd'     => 'sessLeaveMap',
            'instanceId' => $teamId));
    }

    /**
     * 打包[地图广播]消息
     *
     * @param array        $data          数据
     * @param int          $excludeUid    例外用户ID
     *
     * @return array    格式化后的数据
     */
    public static function packMapSend($data, $excludeUid = 0)
    {
        return self::push(array(
            'phpcmd'          => 'mapSendPacket',
            'data'            => $data,
            'excludePlayerId' => $excludeUid));
    }

    /**
     * 打包[地图反射命令]消息
     *
     * @param array        $cmds           命令数据
     *
     * @return array    格式化后的数据
     */
    public static function packMapReflect($cmds)
    {
        return array( // NOTE 无push
            'phpcmd' => 'sendToMapServers',
            'op'     => 0,
            'msg'    => json_encode($cmds));
    }

    /**
     * 将消息存入消息池
     *
     * @param array $message 由'MessagePool::pack*'生成的消息数据
     * @return array    格式化后的数据
     */
    public static function push($message)
    {
        self::$messages[] = $message;
        return $message;
    }

    /**
     * 获取消息池内所有消息
     *
     * @return array 消息列表
     */
    public static function getMessages()
    {
        return self::$messages;
    }
    public static function &getMessagesNoCopy()
    {
        return self::$messages;
    }

    /**
     * 链接rpg-world执行命令
     * @param string $cmd
     */
    public static function remoteCmd($cmd)
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $conn = socket_connect($sock, REMOTE_IP, REMOTE_PORT);

        socket_write($sock, "\n", strlen("\n"));
        $strTmp = '';
        socket_recv($sock, $strTmp, 4098, 0);

        $cmd2 = '@' . json_encode($cmd) . "\n";
        socket_write($sock, $cmd2, strlen($cmd2));
        $str = '';
        socket_recv($sock, $str, 4098, 0);
    }
}
