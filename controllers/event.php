<?php
namespace Controllers;
use Core\Controller;
class event extends Controller{
	public function onConnect(){
		echo '客户端'.$this->fd.'进入' . PHP_EOL;
	}
	public function onClose(){
		echo '客户端'.$this->fd.'离开' . PHP_EOL;
	}
}