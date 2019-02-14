<?php

namespace Task;
use Task\ITask;
use Server\Server;
use Marco\SwooleMarco;
class TestTask implements ITask{
	/**
	 * 启动task任务
	 * @param type $data 任务数据
	 */
	public static function start($data){
		$pack_data = Server::$application->packServerMessageBody(
			SwooleMarco::TASK_TYPE_ASYN_TASK, 
			[
				'task_class'=>self::class,
				'data'=>$data
			]
		);
		return Server::$application->server->task($pack_data);
	}
	/**
	 * 执行异步任务
	 * @param type $data 任务数据
	 * @param type $task_id task任务id
	 * @param type $src_worker_id work进程id
	 */
	public static function task($data,$task_id, $src_worker_id) {
//		echo posix_getpid();
		echo "正在执行异步任务\n";
		//下面模拟请求任务耗时
		sleep(2);
	}
	/**
	 * 执行异步任务
	 * @param type $task_id 异步任务id
	 */
	public static function finish($task_id) {
//		echo posix_getpid();
		echo "异步任务执行完成\n";
	}
}
