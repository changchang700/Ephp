<?php
namespace Server;

use Core\Core;
use Server\Swoole;
use Components\Marco\SwooleMarco;
abstract class SwooleServer extends Swoole{
	/**
     * SwooleServer constructor.
     */
    public function __construct(){
		parent::__construct();
    }

	/**
	 * 服务启动
	 */
    public function start(){
		$set = $this->getServerSet();
		$first_server = $this->getFirstServer();
		
		$socket_ssl = false;
		if(array_key_exists('ssl_cert_file', $first_server) && array_key_exists('ssl_key_file', $first_server)){
			$set['ssl_cert_file'] = $first_server['ssl_cert_file'];
			$set['ssl_key_file'] = $first_server['ssl_key_file'];
			
			$socket_ssl = true;
		}
		
		if ($socket_ssl) {
			$this->server = new \swoole_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol'] | SWOOLE_SSL);
		} else {
			$this->server = new \swoole_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_protocol']);
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
		$this->addServer($first_server['socket_port']);
		$this->beforeSwooleStart();
		$this->server->start();
    }

    /**
     * onSwooleStart
     * @param $serv
     */
    public function onSwooleStart($serv){
		swoole_set_process_name($this->name . '-Master');
    }

    /**
     * onSwooleWorkerStart
     * @param $serv
     * @param $workerId
     */
    public function onSwooleWorkerStart($serv, $workerId){
		$this->workerId = $workerId;
		 //清除apc缓存
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
		if (!$serv->taskworker) {
			swoole_set_process_name($this->name . '-Worker');
        } else {
			swoole_set_process_name($this->name . '-Tasker');
        }
    }

    /**
     * onSwooleConnect
     * @param $serv
     * @param $fd
     */
    public function onSwooleConnect($serv, $fd){
		
    }

    /**
     * 客户端有消息时
     * @param $serv swoole_server对象
     * @param $fd TCP客户端连接的唯一标识符
     * @param $from_id TCP连接所在的Reactor线程ID
     * @param $data 收到的数据内容，可能是文本或者二进制内容
     * @return CoreBase\Controller|void
     */
    public function onSwooleReceive($serv, $fd, $from_id, $data){
		//解析封包
		$pack = $this->getPack($this->getServerPortByFd($fd));
		try {
            $client_data = $pack->unPack($data);
        } catch (\Exception $e) {
            return $pack->errorHandle($e, $fd);
        }
		//解析路由
		$route = $this->getRoute($this->getServerPortByFd($fd));
		try {
			$route->handleClientData($client_data);
			
			$controller_name = $route->getControllerName();
			$method_name = $route->getMethodName();
			$request = null;
			$response = null;
			Core::getInstance()->run($controller_name,$method_name,$client_data,$request,$response);
		} catch (\Exception $e){
			return $route->errorHandle($e, $fd);
		}
    }
	

    /**
     * onSwooleClose
     * @param $serv
     * @param $fd
     */
    public function onSwooleClose($serv, $fd){
		
    }

    /**
     * onSwooleWorkerStop
     * @param $serv
     * @param $worker_id
     */
    public function onSwooleWorkerStop($serv, $worker_id){
		
    }

    /**
     * onSwooleShutdown
     * @param $serv
     */
    public function onSwooleShutdown($serv){
		
    }

    /**
     * onSwooleTask
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return mixed
     */
    public function onSwooleTask($serv, $task_id, $from_id, $data){
		$type = $data['type'] ?? '';
        $message = $data['message'] ?? '';
        switch ($type) {
            case SwooleMarco::MSG_TYPE_SEND_BATCH://发送消息
                foreach ($message['fd'] as $fd) {
                    $this->send($fd, $message['data'], true);
                }
                return null;
            case SwooleMarco::MSG_TYPE_SEND_ALL://发送广播
                foreach ($this->uid_fd_table as $row) {
                    $this->send($row['fd'], $message['data'], true);
                }
                return null;
            case SwooleMarco::MSG_TYPE_SEND_ALL_FD;//发送广播
                foreach ($serv->connections as $fd) {
                    $this->send($fd, $message['data']);
                }
                return null;
        }
    }

    /**
     * onSwooleFinish
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onSwooleFinish($serv, $task_id, $data){

    }

    /**
     * onSwoolePipeMessage
     * @param $serv
     * @param $from_worker_id
     * @param $message
     */
    public function onSwoolePipeMessage($serv, $from_worker_id, $message){
		
    }

    /**
     * onSwooleWorkerError
     * @param $serv
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     */
    public function onSwooleWorkerError($serv, $worker_id, $worker_pid, $exit_code){
		
    }

    /**
     * ManagerStart
     * @param $serv
     */
    public function onSwooleManagerStart($serv){
		swoole_set_process_name($this->name . '-Manager');
    }

    /**
     * ManagerStop
     * @param $serv
     */
    public function onSwooleManagerStop($serv){
		
    }
    /**
     * onPacket(UDP)
     * @param $server
     * @param string $data
     * @param array $client_info
     */
    public function onSwoolePacket($server, $data, $client_info){
		
    }
	/**
	 * 当缓存区达到最高水位时触发此事件。
	 * @param type $server
	 * @param type $fd
	 */
	public function onSwooleBufferFull($server, $fd){

    }
	/**
	 * 当缓存区低于最低水位线时触发此事件。
	 * @param type $server
	 * @param type $fd
	 */
	public function onSwooleBufferEmpty($server, $fd){

    }
	/**
	 * 在onWorkerExit中尽可能地移除/关闭异步的Socket连接
	 * 最终底层检测到Reactor中事件监听的句柄数量为0时退出进程。
	 * @param type $server
	 * @param type $worker_id
	 */
	public function onSwooleWorkerExit($server, $worker_id){

    }
    /**
     * 错误处理函数
     * @param $msg
     * @param $log
     */
    public function onErrorHandel($msg, $log){
		
    }
}
