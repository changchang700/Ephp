<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-20
 * Time: 下午1:39
 */

namespace Components\Marco;


class SwooleMarco
{
    /**
     * 发送消息
     */
    const MSG_TYPE_SEND = 0;
    /**
     * 批量发消息
     */
    const MSG_TYPE_SEND_BATCH = 1;
    /**
     * 全服广播
     */
    const MSG_TYPE_SEND_ALL = 2;
    /**
     * 踢uid下线
     */
    const MSG_TYPE_KICK_UID = 3;
    /**
     * 全服广播
     */
    const MSG_TYPE_SEND_ALL_FD = 4;
}