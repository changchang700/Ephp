<?php
/**
 * 本文件为整个系统入口文件
 */
namespace Server;

use Server\SwooleWebSocketServer;
use Components\Console\Console;
class Server extends SwooleWebSocketServer{
	/**
	 *
	 * @var Server
	 */
	public static $application=null;
	
	public function __construct() {
		parent::__construct();
		self::$application = &$this;
	}
	
	public function run(){
		global $argv;
		$command = $argv[1] ?? "";
		if(!empty($command)){
			switch ($command) {
				case 'start':
					$this->beforeAppStart();
					$option = $argv[2]??"";
					if(!empty($option) && $option == "-d"){
						$this->daemonize = 1;
					}
					Console::gui();
					$this->start();
					break;
				case 'stop':
					exec("ps -ef|grep {$this->name}|grep -v grep|cut -c 9-15|xargs kill -9");
					Console::success("Service stop successfully");
					exit(-1);
					break;
				default:
					Console::help();
					break;
			}
		}else{
			Console::help();
			exit(-1);
		}
	}
	/**
	 * APP启动前执行的
	 */
	private function beforeAppStart() {
		//检查服务状态
		$this->checkAppStatus();
	}
	/**
	 * 检查服务状态
	 */
	private function checkAppStatus(){
		$master_pid = exec("ps -ef | grep {$this->name}-Master| grep -v 'grep ' | awk '{print $2}'");
		if (!empty($master_pid)) {
            Console::Error("Service already running");
            exit(-1);
        }
	}
}
