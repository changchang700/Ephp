<?php

namespace Db;

class Mysql {
	/**
	 * 数据库池实例
	 * @var type 
	 */
	public static $_instance;
	/**
	 * 数据库配置
	 * @var type 
	 */
	private static $dsn = [];
	
	private static $user = '';
	
	private static $pass = '';
	
	private static $driver_options = '';
	/**
	 * 最大的数据库连接数
	 * @var type 
	 */
	const POOLSIZE = 10;
	/**
	 * 数据库连接池
	 * @var type 
	 */
	private $_pools = [];

	public function __construct($dsn, $user=NULL, $pass=NULL, $driver_options=NULL) {
		for ($i=0; $i < self::POOLSIZE; $i++) {
			self::$dsn = $dsn;
			self::$user = $user;
			self::$pass = $pass;
			self::$driver_options = $driver_options;
            array_push($this->_pools, $this->connect());
        }
	}
	
	public static function getInstance(){
		if(self::$_instance instanceof self){
			return self::$_instance;
		}else{
			$dsn = 'mysql:dbname=test;host=127.0.0.1';
			$user = 'root';
			$pass = 'root';
			$options = [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING,
				\PDO::ATTR_PERSISTENT => true
			];
			return self::$_instance = new self($dsn,$user,$pass);
		}
		
	}
	
    private function get(){
        if (count($this->_pools) > 0) {
            $conn =  array_pop($this->_pools);
            return $conn;
        } else {
            throw new \Exception ( "数据库连接池中已无链接资源，请稍后重试！" );
        }
    }
	
	
    //将用完的数据库链接资源放回到数据库连接池
    private function set($conn){
        if (count($this->_pools) >= self::POOLSIZE) {
            throw new \Exception ( "数据库连接池已满！" );
        } else {
            array_push($this->_pools, $conn);
        }
	}
	
	public function query($sql){
        try {
            $conn = $this->get(); 
            $res = $conn->query($sql);
			$error = $conn->errorInfo();
			if($error['1']==2006){
				//清理数据
				unset($res);
				unset($conn);
				unset($error);
				//生成新的连接
				$conn = $this->connect();
				//把新的连接归还到数据库池
				$this->set($conn);
				//再次进行查询
				return $this->query($sql);
			} else {
				$this->set($conn);
			}
            return $res;
        } catch (\Exception $e) {
            print 'error:' . $e->getMessage();
        }
	}
	
	public function connect(){
		try {
			$conn = new \PDO(self::$dsn, self::$user, self::$pass, self::$driver_options);
			return $conn;
		} catch (\Exception $exc) {
			unset($conn);
			unset($exc);
			return $this->connect();
		}
	}
}

for($i=0;$i<=100;$i++){
	$data = Mysql::getInstance()->query('select * from wolive_business');
	echo "内存使用情况:".(memory_get_usage()/1024)."\n";
	sleep(2);
	var_dump($data);
}