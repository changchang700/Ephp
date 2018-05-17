<?php
namespace Core;

use Core\Core;
class Controller{
	/**
	 * 用户fd
	 * @var type 
	 */
	public $fd;
	/**
	 * 使用TCP WEBSOCKET协议
	 * 收到的客户端数据
	 * @var type 
	 */
	public $client_data;
	/**
     * http response
     * @var \swoole_http_request
     */
    protected $request;
    /**
     * http response
     * @var \swoole_http_response
     */
    protected $response;
	
	/**
	 * 设置数据
	 * @param type $fd fd
	 * @param type $client_data 客户端数据
	 * @param type $request http请求
	 * @param type $response http 响应
	 */
	public function before(&$client_data,&$request,&$response) {
		$this->client_data = $client_data;
		$this->request = $request;
		$this->response = $response;
	}
	
	/**
	 * 释放对象数据
	 */
	public function after(){
        $this->fd = null;
        $this->client_data = null;
        $this->request = null;
        $this->response = null;
        Core::getInstance()->revertController($this);
    }
}