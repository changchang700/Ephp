<?php

namespace Task;

interface ITask{
	
	public static function start($data);

	public static function task($data,$task_id, $src_worker_id);

	public static function finish($task_id);
}
