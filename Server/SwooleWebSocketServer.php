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
abstract class SwooleWebSocketServer extends SwooleHttpServer
{
    /**
     * @var array
     */
    protected $fdRequest = [];
    protected $custom_handshake = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 启动
     */
    public function start()
    {
		if (!$this->server_manger->enable_swoole_websocket_erver) {
            parent::start();
            return;
        }
		$first_server = $this->server_manger->getFirstServer();
        $this->server = new \swoole_websocket_server($first_server['socket_name'], $first_server['socket_port']);
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
		//websocket独有的回调
		$this->server->on('Open', [$this, 'onSwooleWSOpen']);
		$this->server->on('Message', [$this, 'onSwooleWSMessage']);
		$this->server->on('HandShake', [$this, 'onSwooleWSHandShake']);
		
		$this->server_manger->addServer($this,$first_server['socket_port']);
        $this->beforeSwooleStart();
        $this->server->start();
    }

    /**
     * @param $serv
     */
    public function onSwooleWorkerStop($serv, $workerId)
    {
		
    }

    /**
     * websocket连接上时
     * @param $server
     * @param $request
     */
    public function onSwooleWSOpen($server, $request)
    {
		
    }

    /**
     * websocket收到消息时
     * @param $server
     * @param $frame
     */
    public function onSwooleWSMessage($server, $frame)
    {
		
    }

    /**
     * ws握手连接
     * @param $request
     * @param $response
     * @return bool
     */
    public function onSwooleWSHandShake(\swoole_http_request $request, \swoole_http_response $response){
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

