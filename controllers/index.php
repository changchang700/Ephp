<?php
namespace Controllers;
use Core\Controller;
use Task\TestTask;
use Db\MysqlCoroutine;
class index extends Controller{
	public function index(){
//		MysqlCoroutine::init([
//                'test' => [
//                        'serverInfo' => ['host' => '127.0.0.1', 'user' => 'root', 'password' => '0wqgkpHh', 'database' => 'admin_v', 'charset' => 'utf8'],
//                        'maxSpareConns' => 5,
//                        'maxConns' => 10
//                ],
//        ]);
//		$swoole_mysql = MysqlCoroutine::fetch('test');
//        $ret = $swoole_mysql->query('show tables;');
//        MysqlCoroutine::recycle($swoole_mysql);
//		var_dump($ret);
		return $this->send("hello world");
	}
	
	public function test(){
		return $this->send('test method');
	}
	
	//send系列函数测试
	public function push(){
		return $this->sendToAllFd("this is message");
	}
	
	//异步任务测试
	public function task(){
		TestTask::start('aaaaa');
		return $this->send('task is start');
	}
	
	//定时器测试
	public function timer(){
		\Timer\Timer::getInstance(1)->after(1000, function(){
			echo "测试成功\n";
		});
		return $this->send('timer is success');
	}
}