<?php
/**
 * 包含http服务器
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-29
 * Time: 上午9:42
 */

namespace Server;

use Server\SwooleHttpServer;
use Core\Core;
abstract class SwooleWebSocketServer extends SwooleHttpServer{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 启动
     */
    public function start(){
		//如果没有启动websocket，则启动上层服务
		if (!$this->enable_swoole_websocket_erver) {
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
			$this->server = new \swoole_websocket_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol'] | SWOOLE_SSL);
		} else {
			$this->server = new \swoole_websocket_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol']);
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
		//websocket独有的回调
		$this->server->on('Open', [$this, 'onSwooleOpen']);
		$this->server->on('Message', [$this, 'onSwooleMessage']);
		$this->server->on('HandShake', [$this, 'onSwooleHandShake']);
		
		$this->addServer($first_server['socket_port']);
        $this->beforeSwooleStart();
        $this->server->start();
    }

	/**
     * websocket连接上时
     * @param $server
     * @param $request
     */
    public function onSwooleOpen($server, $request){
		
    }

    /**
     * websocket收到消息时
     * @param $server
     * @param $frame
     */
    public function onSwooleMessage($server, $frame){
		//解析封包
		$pack = $this->getPack($this->getServerPortByFd($frame->fd));
		try {
            $client_data = $pack->unPack($frame->data);
        } catch (\Exception $e) {
            return $pack->errorHandle($e, $frame->fd);
        }
		//解析路由
		$route = $this->getRoute($this->getServerPortByFd($frame->fd));
		try {
			$route->handleClientData($client_data);
			
			$controller_name = $route->getControllerName();
			$method_name = $route->getMethodName();
			$request = null;
			$response = null;
			Core::getInstance()->run($controller_name,$method_name,$client_data,$request,$response);
		}catch(\Exception $e){
			return $route->errorHandle($e, $frame->fd);
		}
    }

    /**
     * ws握手连接
     * @param $request
     * @param $response
     * @return bool
     */
    public function onSwooleHandShake(\swoole_http_request $request, \swoole_http_response $response){
		//此处可以设置用户的验证代码，是否连接
			/**TODO**/
		// websocket握手连接算法验证
		$secWebSocketKey = $request->header['sec-websocket-key'];
		$patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
		if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
			$response->end();
			return false;
		}
		$key = base64_encode(sha1(
			$request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
			true
		));

		$headers = [
		 'Upgrade' => 'websocket',
		 'Connection' => 'Upgrade',
		 'Sec-WebSocket-Accept' => $key,
		 'Sec-WebSocket-Version' => '13',
		];

		if (isset($request->header['sec-websocket-protocol'])) {
		 $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
		}

		foreach ($headers as $key => $val) {
		 $response->header($key, $val);
		}
		$response->status(101);
		$response->end();
		return true;
    }
}

