<?php
namespace Controllers;
use Core\Controller;
class event extends Controller{
	public function onConnect(){
		echo '进入' . $this->fd . PHP_EOL;
	}
	public function onClose(){
		echo '离开' . $this->fd . PHP_EOL;
	}
}