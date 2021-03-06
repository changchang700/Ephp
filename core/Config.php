<?php
namespace Core;

class Config{
	/**
	 * 单例本身
	 * @var Core\Config
	 */
	public static $instance;
	/**
	 * 保存所有配置文件
	 * @var type 
	 */
	private $config = [];
	/**
	 * 获取本身实例
	 * @return Config
	 */
	public static function get_instance(){
		if(!self::$instance instanceof self){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		$path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
		$list = scandir($path);
		foreach ($list as $file) {
			if(is_file($path.$file)){
				$name = substr($file, 0, strlen($file)-4);
				$this->config[$name] = require_once ($path.$file);
			}
		}
	}

	/**
	 * 获取应用配置
	 * 如果key为空则获取所有配置参数
	 * @param type $key 配置文件key
	 * @return type array
	 */
	public function get($key=null){
		if(null!==$key){
			if(isset($this->config[$key])){
				return $this->config[$key];
			}else{
				return null;
			}
		}else{
			return $this->config;
		}
	}
}