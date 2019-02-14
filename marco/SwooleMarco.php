<?php

/**
 * 对应命令
 * @author  木木
 * @link 
 */

namespace Marco;

class SwooleMarco {

	/**
	 * 发送消息给所有FD
	 */
	const MSG_TYPE_SEND_TO_ALL_FD = 1;

	/**
	 * 批量发消息给UIDS
	 */
	const MSG_TYPE_SEND_TO_UIDS = 2;

	/**
	 * 发送消息给所有UID
	 */
	const MSG_TYPE_SEND_TO_ALL_UID = 3;

	/**
	 * 异步任务
	 */
	const TASK_TYPE_ASYN_TASK = 4;

	/**
	 * 异步任务
	 */
	const TASK_TYPE_ASYN_FINISH = 5;

}
