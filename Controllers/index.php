<?php
namespace Controllers;
use Core\Controller;
class index extends Controller{
	public function index(){
		$this->response->end("hello world");
	}
	public function test(){
		var_dump($this->client_data);
	}
}