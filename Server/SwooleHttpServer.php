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
		if (!$this->enable_swoole_http_erver) {
            parent::start();
            return;
        }
		$first_server = $this->getFirstServer();
        $this->server = new \swoole_http_server($first_server['socket_name'], $first_server['socket_port']);
        $this->server->set($this->getServerSet());
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
			Core::getInstance()->run($controller_name,$method_name,$client_data,$request, $response);
		} catch (\Exception $e){
			$route->errorHttpHandle($e, $request, $response);
		}
    }
}