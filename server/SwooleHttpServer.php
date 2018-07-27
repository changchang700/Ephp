<?php
namespace Server;

use Server\SwooleServer;
use Core\Core;
abstract class SwooleHttpServer extends SwooleServer{
    public function __construct(){
		parent::__construct();
    }

    /**
     * 启动
     */
    public function start(){
		//如果没有启动http服务则启动上层服务
		if (!$this->enable_swoole_http_erver) {
            parent::start();
            return;
        }
		$set = $this->getServerSet();		
		$first_server = $this->getFirstServer();
		
		$socket_ssl = false;
		if(array_key_exists('ssl_cert_file', $first_server) && array_key_exists('ssl_key_file', $first_server)){
			$set['ssl_cert_file'] = $first_server['ssl_cert_file'];
			$set['ssl_key_file'] = $first_server['ssl_key_file'];
			
			$socket_ssl = true;
		}
		
		if ($socket_ssl) {
            $this->server = new \swoole_http_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol'] | SWOOLE_SSL);
        } else {
            $this->server = new \swoole_http_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol']);
        }
		
		$buf_set  = $this->getProbufSet($first_server['socket_port']);
		
		$final_set = array_merge($set,$buf_set);
		
		$this->server->set($final_set);
		$this->server->on('Start', [$this, 'onSwooleStart']);
		$this->server->on('WorkerStart', [$this, 'onSwooleWorkerStart']);
		$this->server->on('Connect', [$this, 'onSwooleConnect']);
		$this->server->on('Receive', [$this, 'onSwooleReceive']);
		$this->server->on('Close', [$this, 'onSwooleClose']);
		$this->server->on('WorkerStop', [$this, 'onSwooleWorkerStop']);
		$this->server->on('Task', [$this, 'onSwooleTask']);
		$this->server->on('Finish', [$this, 'onSwooleFinish']);
		$this->server->on('PipeMessage', [$this, 'onSwoolePipeMessage']);
		$this->server->on('WorkerError', [$this, 'onSwooleWorkerError']);
		$this->server->on('ManagerStart', [$this, 'onSwooleManagerStart']);
		$this->server->on('ManagerStop', [$this, 'onSwooleManagerStop']);
		$this->server->on('BufferFull', [$this, 'onSwooleBufferFull']);
		$this->server->on('BufferEmpty', [$this, 'onSwooleBufferEmpty']);
		$this->server->on('WorkerExit', [$this, 'onSwooleWorkerExit']);
		$this->server->on('Packet', [$this, 'onSwoolePacket']);
		$this->server->on('Shutdown', [$this, 'onSwooleShutdown']);
		//http独有响应回调
		$this->server->on('Request', [$this, 'onSwooleRequest']);
		
		$this->addServer($first_server['socket_port']);
        $this->beforeSwooleStart();
        $this->server->start();
    }

	/**
     * http服务器发来消息
     * @param $request http请求对象
     * @param $response http回应对象
     */
    public function onSwooleRequest($request, $response){
		//解析路由
		$route = $this->getRoute($this->getServerPortByFd($request->fd));
		try {
			$route->handleClientRequest($request);
			
			$controller_name = $route->getControllerName();
			$method_name = $route->getMethodName();
			$client_data = null;
			Core::getInstance()->run($controller_name,$method_name,$client_data,$request,$response);
		} catch (\Exception $e){
			$route->errorHttpHandle($e, $request, $response);
		}
    }
}