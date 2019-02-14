<?php
namespace Core;

use Server\Server;

/**
 * @method \Server\Server  bindUid($fd, $uid)
 * @method \Server\Server  getFdFromUid($uid)
 * @method \Server\Server  getFdInfo($fd)
 * @method \Server\Server  getServerPortByFd($fd)
 * @method \Server\Server  getSocketTypeByFd($fd)
 * @method \Server\Server  getUidFromFd($fd)
 * @method \Server\Server  getWorkerId()
 * @method \Server\Server  isTaskWorker()
 * @method \Server\Server  isWorker()
 * @method \Server\Server  kickUid($uid)
 * @method \Server\Server  sendToAllFd($data)
 * @method \Server\Server  sendToAllUid($data)
 * @method \Server\Server  sendToUid($uid, $data)
 * @method \Server\Server  sendToUids($uids, $data)
 * @method \Server\Server  unBindUid($uid, $fd)
 */
class Controller{
	/**
	 * 当前客户端
	 * @var type 
	 */
	public $fd = null;
	/**
	 * 使用TCP WEBSOCKET协议
	 * 收到的客户端数据
	 * @var type 
	 */
	public $client_data = null;
	/**
     * http response
     * @var \swoole_http_request
     */
    protected $request = null;
    /**
     * http response
     * @var \swoole_http_response
     */
    protected $response = null;
	
	/**
	 * 设置数据
	 * 本方法无需手动执行
	 * 系统会自动执行
	 * @param type $fd fd 客服的标识符
	 * @param type $client_data 客户端数据
	 * @param type $request http请求
	 * @param type $response http 响应
	 */
	public function before($fd,$client_data,$request,$response) {
		$this->fd = $fd;
		$this->client_data = $client_data;
		$this->request = $request;
		$this->response = $response;
	}
	
	/**
	 * 响应客户端
	 * 如果是http请求执行此请求后，后续代码不会生效
	 * @param type $data
	 * @return type
	 */
	final public function send($data){
		if(null==$this->request && null==$this->response){
			return Server::$application->send($this->fd, $data);
		}else{
			return $this->response->end($data);
		}
	}

	/**
	 * 释放对象数据
	 * 本方法无需手动执行
	 * 系统会自动执行
	 */
	public function after(){
		$this->fd = null;
        $this->client_data = null;
        $this->request = null;
        $this->response = null;
    }
	/**
	 * 调用swoole对象的方法
	 * @param type $name 方法名称
	 * @param type $arguments 参数
	 * @return type
	 */
	public function __call($name, $arguments) {
		return call_user_func_array(array(Server::$application, $name), $arguments);
	}
}