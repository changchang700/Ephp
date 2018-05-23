<?php
namespace Controllers;
use Core\Controller;
use Server\Server;
class index extends Controller{
	public function index(){
		Server::$application->sendToAllFd("lllll");
//		$this->response->end("hello world");
	}
	public function test(){
		Server::$application->sendToAllFd("lllll");
	}
}