<?php
/**
 * 对应命令
 * @author  木木
 * @link 
 */
namespace Components\Marco;


class SwooleMarco{
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
     * 全服广播
     */
    const MSG_TYPE_SEND_ALL_FD = 3;
}