<?php
/**
 * server类，本类继承SwooleWebSocketServer类
 * 本类也是最终类，无法被继承
 * @author Mumu <2107898148@qq.com>
 * @date 2018年7月26日
 * @time 15:11:28
 */
namespace Server;

use Server\SwooleWebSocketServer;
use Components\Console\Console;

/**
 * server类
 */
final class Server extends SwooleWebSocketServer{
	/**
	 * 保存整个server实例
	 * 方便调用
	 * @var Server
	 */
	public static $application=null;
	
	/**
	 * 初始化服务,配置相关参数
	 */
	public function __construct() {
		parent::__construct();
		self::$application = $this;
	}
	/**
	 * 启动server
	 */
	public function run(){
		global $argv;
		if(!preg_match("/cli/i", php_sapi_name()) ? true : false){
			exit('Please run at the command');
		}
		$command = $argv[1] ?? "";
		if(!empty($command)){
			switch ($command) {
				case 'start':
					$master_pid = exec("ps -ef | grep {$this->name}-Master| grep -v 'grep ' | awk '{print $2}'");
					if (!empty($master_pid)) {
						Console::Error("Service already running");
						exit(-1);
					}
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
}
