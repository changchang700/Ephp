<?php
use Server\Server;

/**
 * 整个项目入口文件
 * 本类可初始化work相关数据
 * 包括自定义进程、自定义定时器等
 */
final class App extends Server{
	
	public function __construct() {
		parent::__construct();
	}

		/**
	 * @param type $serv
	 * @param type $worker_id
	 */
	public function onSwooleWorkerStart($serv, $worker_id) {
		parent::onSwooleWorkerStart($serv, $worker_id);
		//定时器
//		if($this->worker_id == 0){
//			$data = 'zhangsan';
//			Timer\Timer::getInstance(1)->tick(1000, function()use($data){
//				echo "hello {$data}\n";
//			});
//		}
	}
	public function beforeSwooleStart() {
		parent::beforeSwooleStart();
		//注册自定义进程
		$process = (new Process\TestProcess('TestProcess'))->getProcess();
		$this->server->addProcess($process);
		$process->write("nihao");
	}
}