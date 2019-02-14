<?php
namespace Core;

use Core\Core;
use Console\Console;
class Core{
    private static $instance;
    private $pool = [];

    public function __construct(){
        self::$instance = &$this;
    }

    /**
     * 获取单例
     * @return Core
     */
    public static function getInstance(){
        if (self::$instance == null) {
            new Core();
        }
        return self::$instance;
    }

	/**
	 * 执行控制方法
	 * @param string $controller_name 控制器名称
	 * @param string $method_name 控制器方法
	 * @param type $fd 客户端标识符
	 * @param type $client_data 客户端数据
	 * @param type $request http请求对象
	 * @param type $response http响应对象
	 * @throws \Exception 错误提示
	 */
    public function run(&$controller_name,&$method_name, &$fd, &$client_data,&$request, &$response){
		//定义默认方法
		if ($controller_name == null){
			$controller_name = 'index';
		}
		if($method_name == null){
			$method_name = 'index';
		}
        $controller_names = $this->pool[$controller_name] ?? null;
        if ($controller_names == null) {
            $controller_names = $this->pool[$controller_name] = new \SplQueue();
        }
        if ($controller_names->isEmpty()) {
			$class_name = "Controllers\\$controller_name";
			if (class_exists($class_name)) {
				$obj = new $class_name;
				$obj->core_name = $controller_name;
				if(method_exists($obj, $method_name)){
					try {
						$obj->before($fd, $client_data,$request,$response);
						$obj->$method_name();
						$obj->after();
						//归还到池中
						$this->pool[$obj->core_name]->push($obj);
					} catch (\Exception $exc) {
						Console::warning($exc->getMessage(),33);
					}
				}else{
					throw new \Exception("Not find the method \"{$method_name}\"");
				}
			} else {
				throw new \Exception("Not find the controller \"{$controller_name}\"");
			}
		}else{
			//从池中获取
			$obj = $controller_names->shift();
			$obj->core_name = $controller_name;
			if(method_exists($obj, $method_name)){
				try {
					$obj->before($fd, $client_data,$request,$response);
					$obj->$method_name();
					$obj->after();
					//归还到池中
					$this->pool[$obj->core_name]->push($obj);
				} catch (\Exception $exc) {
					Console::warning($exc->getMessage(),33);
				}
			}else{
				throw new \Exception("Not find the method \"{$method_name}\"");
			}
		}
    }
}