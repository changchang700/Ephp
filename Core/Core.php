<?php
namespace Core;

use Core\Core;
use Components\Console\Console;
class Core{
    private static $instance;
    private $pool = [];
    private $pool_count = [];

    public function __construct(){
        self::$instance = &$this;
    }

    /**
     * 获取单例
     * @return ControllerFactory
     */
    public static function getInstance(){
        if (self::$instance == null) {
            new Core();
        }
        return self::$instance;
    }

    /**
	 * 执行控制器
	 * @param type $controller_name 控制器名称
	 * @param string $method_name 控制器方法
	 * @param type $params 参数
	 * @return type
	 */
    public function run(&$controller_name,&$method_name,&$client_data,&$request, &$response){
		//定义默认方法
        if ($controller_name == null) $controller_name = 'index';
		if($method_name == null) $method_name = 'index';
        $controller_names = $this->pool[$controller_name] ?? null;
        if ($controller_names == null) {
            $controller_names = $this->pool[$controller_name] = new \SplQueue();
        }
        if (!$controller_names->isEmpty()) {
            $obj = $controller_names->shift();
			return $this->excute($controller_name,$obj, $method_name, $client_data,$request, $response);
        }
        if (class_exists($controller_name)) {
            $obj = new $controller_name;
            if ($obj instanceof Controller) {
				return $this->excute($controller_name,$obj, $method_name, $client_data,$request, $response);
            }
        }
        $controller_name_new = str_replace('/', '\\', $controller_name);
		$class_name = "Controllers\\$controller_name_new";
		if (class_exists($class_name)) {
			$obj = new $class_name;
			return $this->excute($controller_name,$obj, $method_name, $client_data,$request, $response);
		} else {
			throw new \Exception("Not find the controller \"{$controller_name}\"");
		}
    }
	/**
	 * 执行控制器方法
	 * @param type $obj 控制器类对象
	 * @param type $method_name 控制器方法名称
	 * @param type $params 参数
	 * @param type $controller_name 控制器名称
	 */
	public function excute(&$controller_name,&$controller_obj,&$method_name,&$client_data,&$request, &$response){
		$controller_obj->core_name = $controller_name;
		$this->addNewCount($controller_name);
		if(method_exists($controller_obj, $method_name)){
			try {
				$controller_obj->before($client_data,$request,$response);
				$controller_obj->$method_name();
				$controller_obj->after();
			} catch (\Exception $exc) {
				Console::warning($exc->getMessage(),33);
			}
		}else{
			throw new \Exception("Not find the method \"{$method_name}\"");
		}
	}

	/**
	 * 归还
	 * @param type $controller_name
	 */
    public function revertController($controller_name){
        $this->pool[$controller_name->core_name]->push($controller_name);
    }

    private function addNewCount($name){
        if (isset($this->pool_count[$name])) {
            $this->pool_count[$name]++;
        } else {
            $this->pool_count[$name] = 1;
        }
    }

    /**
     * 获取状态
     */
    public function getStatus(){
        $status = [];
        foreach ($this->pool as $key => $value) {
            $status[$key . '[pool]'] = count($value);
            $status[$key . '[new]'] = $this->pool_count[$key] ?? 0;
        }
        return $status;
    }
}