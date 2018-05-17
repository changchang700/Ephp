<?php
namespace Server;

use Core\Config;
use Core\ServerManger;
use Core\Core;
abstract class SwooleServer{
    /**
     * 应用版本
     */
    const version = "1.0";
    /**
     * 应用名称
     * @var string
     */
    public $name = 'Ephp';
    /**
     * worker数量
     * @var int
     */
    public $worker_num = 0;
	/**
	 * task进程数量
	 * @var type 
	 */
    public $task_num = 0;
    /**
	 * swoole server 实例
	 * @var type 
	 */
    public $server;
	/**
	 * 所有配置项
	 * @var type 
	 */
	public $config;
	/**
	 * 是否进程守护
	 * @var type 
	 */
	public $daemon;
	/**
	 * 服务器管理对象
	 * @var type 
	 */
	public $serverManger;
	/**
	 * 日志对象
	 * @var type 
	 */
	public $Log;
	/**
     * 共享内存表
     * @var \swoole_table
     */
    protected $uid_fd_table;
    /**
	 * 共享内存表
     * @var \swoole_table
     */
    protected $fd_uid_table;

    /**
	 * 最大连接数
     * @var int
     */
    protected $max_connection;

	/**
     * SwooleServer constructor.
     */
    public function __construct(){
		//获取应用配置
		$this->config = (new Config())->get();
		//设置应用名称
		$this->name = $this->config['name'];
		//设置服务器管理
		$this->serverManger = new ServerManger();
    }

	/**
	 * 设置服务器配置参数
	 * @return type 返回配置参数
	 */
    public function getServerSet(){
        $set = (new Config())->get('set');
        $this->worker_num = $set['worker_num'];
        $this->task_num = $set['task_worker_num'];
		$this->max_connection = $set['max_connection'] ?? 102400;
		$set['daemonize'] = $this->daemon;
		return $set;
    }

	/**
	 * 服务启动
	 */
    public function start(){
		$first_server = $this->serverManger->getFirstServer();
        $this->server = new \swoole_server($first_server['socket_name'], $first_server['socket_port'], SWOOLE_PROCESS, $first_server['socket_type']);
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
		$this->serverManger->addServer($this,$first_server['socket_port']);
		$this->beforeSwooleStart();
		$this->server->start();
    }

    /**
     * start前的操作
     */
    public function beforeSwooleStart(){
        //创建uid<->fd共享内存表
        $this->createUidTable();
    }

    /**
     * 创建uid<->fd共享内存表
     */
    protected function createUidTable(){
        $this->uid_fd_table = new \swoole_table($this->max_connection);
        $this->uid_fd_table->column('fd', \swoole_table::TYPE_INT, 8);
        $this->uid_fd_table->create();

        $this->fd_uid_table = new \swoole_table($this->max_connection);
        $this->fd_uid_table->column('uid', \swoole_table::TYPE_STRING, 32);
        $this->fd_uid_table->create();
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
		$pack = $this->serverManger->getPack($this->getServerPortByFd($fd));
		try {
            $client_data = $pack->unPack($data);
        } catch (\Exception $e) {
            $pack->errorHandle($e, $fd);
            return null;
        }
		$route = $this->serverManger->getRoute($this->getServerPortByFd($fd));
		try {
			$route->handleClientData($client_data);
			$controller_name = $route->getControllerName();
			$method_name = $route->getMethodName();
			$request = null;
			$response = null;
			Core::getInstance()->run($controller_name,$method_name,$client_data,$request,$response);
		} catch (\Exception $e){
			$route->errorHandle($e, $fd);
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
    /**
     * 判断这个fd是不是一个WebSocket连接
	 * 用于区分tcp和websocket
     * 握手后才识别为websocket
     * @param $fdinfo
     * @return bool
     * @throws \Exception
     * @internal param $fd
     */
    public function isWebSocket($fdinfo){
        if (empty($fdinfo)) {
            throw new \Exception('fd not exist');
        }
        if (array_key_exists('websocket_status', $fdinfo)) {
            return $fdinfo['server_port'];
        }
        return false;
    }
    /**
	 * 通过fd获取客户端信息
     * @param $fd
     * @return mixed
     */
    public function getFdInfo($fd){
        $fdinfo = $this->server->connection_info($fd);
        return $fdinfo;
    }
	
	/** 根据fd获取服务器端口
     * @param $fd
     * @return mixed
     */
    public function getServerPortByFd($fd){
        return $this->server->connection_info($fd)['server_port'];
    }

    /**
     * 设置客户端连接为保护状态
	 * 不被心跳线程切断。
     * @param $fd fd
     */
    public function protect($fd){
        $this->server->protect($fd);
    }
	
    /**
     * 发送数据
     * @param $fd
     * @param $data
     */
    public function send($fd, $data){
        if (!$this->server->exist($fd)) {
            return null;
        }
		$fdinfo = $this->getFdInfo($fd);
		if($this->isWebSocket($fdinfo)){
			return $this->server->push($fd, $data);
		}else{
			return $this->server->send($fd, $data);
		}
    }
    /**
     * 服务器主动关闭链接
     * close fd
     * @param $fd
     */
    public function close($fd){
        $this->server->close($fd);
    }
    /**
     * 通过Uid获取fd
     * @param $uid
     * @return mixed
     */
    public function getFdFromUid($uid){
        return $this->uid_fd_table->get($uid, 'fd');
    }

    /**
     * 通过fd获取uid
     * @param $fd
     * @return mixed
     */
    public function getUidFromFd($fd){
        return $this->fd_uid_table->get($fd, 'uid');
    }
	/**
     * 将fd绑定到uid
	 * uid不能为0
     * @param $fd fd
     * @param $uid 用户id
     */
    public function bindUid($fd, $uid){
        $this->uid_fd_table->set($uid, ['fd' => $fd]);
        $this->fd_uid_table->set($fd, ['uid' => $uid]);
    }

    /**
     * 解绑uid
	 * 链接断开自动解绑
     * @param $uid 用户ID
     */
    public function unBindUid($uid, $fd){
        //更新共享内存
        $this->uid_fd_table->del($uid);
        $this->fd_uid_table->del($fd);
    }
}
