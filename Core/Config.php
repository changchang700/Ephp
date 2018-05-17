<?php
namespace Core;
class Config{
	private $config;
	
	public function __construct() {
		$this->config = include __DIR__.'/../Config/app.php';
	}
	
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