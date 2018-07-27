<?php
/**
 * 整个应用服务基类
 * 此类执行swoole启动前的初始化操作
 * @author Mumu
 * @link https://www.alilinet.com
 */
namespace Server;

use Core\Config;
use Components\Marco\SwooleMarco;
abstract class Swoole{
	/**
	 * socket类型
	 * socket_server
	 */
	const socket_server = 1;
	/**
	 * socket类型
	 * socket_http_server
	 */
	const socket_http_server = 2;
	/**
	 * socket类型
	 * socket_ws_server
	 */
	const socket_ws_server = 3;
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
	 * workerid
	 * @var type 
	 */
	public $workerId;
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
    public $server = null;
	/**
	 * 是否进程守护
	 * @var type 
	 */
	public $daemonize;
	/**
	 * 是否开启tcp服务
	 * @var type 
	 */
	public $enable_swoole_tcp_server = false;
	/**
	 * 是否开启http服务
	 * @var type 
	 */
	public $enable_swoole_http_erver = false;
	/**
	 * 是否开启websocket服务
	 * @var type 
	 */
	public $enable_swoole_websocket_erver = false;
	/**
	 * 所有配置
	 * @var type 
	 */
	public $config;
	/**
	 * 端口配置
	 * @var type 
	 */
	public $port_config;
	/**
	 * 端口对应到路由
	 * @var type 
	 */
	public $routes = [];
	/**
	 * 端口对应到封包
	 * @var type 
	 */
	public $packs = [];
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
	
	public function __construct() {
		//设置所有配置
		$this->config = Config::get_instance()->get();
		//设置应用名称
		$this->name = $this->config['name'];
		//设置worker_num
		$this->worker_num = $this->config['set']['worker_num'];
		//设置task_worker_num
        $this->task_num = $this->config['set']['task_worker_num'];
		//设置max_connection
		$this->max_connection = $this->config['set']['max_connection'] ?? 102400;
		//设置daemonize
		$this->daemonize = $this->config['set']['daemonize'];
		//设置端口配置
		$port_config_before = $this->config['server'];
		foreach($port_config_before as $value){
            if($value['socket_type'] == self::socket_ws_server){
                $this->enable_swoole_websocket_erver = true;
            }elseif($value['socket_type'] == self::socket_http_server) {
                $this->enable_swoole_http_erver = true;
            }else{
                $this->enable_swoole_tcp_server = true;
            }
			$port_config_after[$value['socket_port']] = $value;
        }
		$this->port_config = $port_config_after;
	}
	
    /**
	 * 启动swoole之前操作
	 */
    protected function beforeSwooleStart(){
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
	 * 获取常规设置参数
	 * 设置swoole set配置参数
	 * @return type 返回配置参数
	 */
    protected function getServerSet(){
        $set = $this->config['set'];
		//根据命令行设置的-d参数是否启动进程守护
		$set['daemonize'] = $this->daemonize;
		return $set;
    }
	
	/**
	 * 添加监听端口服务
	 * @param type $first_port 第一个端口
	 * @throws \Exception
	 */
    protected function addServer($first_port){
        foreach ($this->port_config as $value) {
            if ($value['socket_port'] == $first_port) continue;
			if($value['status'] == 'stop') continue;
			//获取配置参数
			$set = $this->getServerSet();
			
			$socket_ssl = false;
			if(array_key_exists('ssl_cert_file', $value) && array_key_exists('ssl_key_file', $value)){
				$set['ssl_cert_file'] = $value['ssl_cert_file'];
				$set['ssl_key_file'] = $value['ssl_key_file'];
				
				$socket_ssl = true;
			}
            
            if ($value['socket_type'] == self::socket_http_server || $value['socket_type'] == self::socket_ws_server) {
				if ($socket_ssl) {
                    $port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol'] | SWOOLE_SSL);
                } else {
                    $port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol']);
                }
                if($port == false) {
                    throw new \Exception("{$value['socket_port']}端口创建失败");
                }
                if($value['socket_type'] == self::socket_http_server){
                    $set['open_http_protocol'] = true;
					
                    $buf_set  = $this->getProbufSet($value['socket_port']);
		
					$final_set = array_merge($set,$buf_set);

					$port->set($final_set);
					
                    $port->on('Request', [$this, $value['request'] ?? 'onSwooleRequest']);
                    $port->on('Handshake', function (){
                        return false;
                    });
                }else{
                    $set['open_http_protocol'] = true;
                    $set['open_websocket_protocol'] = true;
					
					$buf_set  = $this->getProbufSet($value['socket_port']);
		
					$final_set = array_merge($set,$buf_set);

					$port->set($final_set);
					
                    $port->on('Open', [$this, $value['open'] ?? 'onSwooleOpen']);
                    $port->on('Message', [$this, $value['message'] ?? 'onSwooleMessage']);
                    $port->on('Handshake', [$this, $value['handshake'] ?? 'onSwooleHandShake']);
                }
            }else{
                if ($socket_ssl) {
                    $port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol'] | SWOOLE_SSL);
                } else {
                    $port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol']);
                }
                if($port == false){
                    throw new \Exception("{$value['socket_port']}端口创建失败");
                }
                
				$buf_set  = $this->getProbufSet($value['socket_port']);
		
				$final_set = array_merge($set,$buf_set);

				$port->set($final_set);
				
                $port->on('Connect', [$this, $value['connect'] ?? 'onSwooleConnect']);
                $port->on('Receive', [$this, $value['receive'] ?? 'onSwooleReceive']);
                $port->on('Close', [$this, $value['close'] ?? 'onSwooleClose']);
                $port->on('Packet', [$this, $value['packet'] ?? 'onSwoolePacket']);
            }
        }
    }
	
	/**
	 * 获取协议的包头设置参数
	 * @param type $port
	 * @return type
	 */
	public function getProbufSet($port)
    {
		$set = $this->getPack($port);
        if ($set == null) {
            return [];
        }
        return $set->getProbufSet();
    }
	/**
	 * 获取第一个服务
	 * 及其类型
	 * @return type
	 */
	protected function getFirstServer(){
        if ($this->enable_swoole_websocket_erver) {
            $type = self::socket_ws_server;
        } else if ($this->enable_swoole_http_erver) {
            $type = self::socket_http_server;
        } else {
            $type = self::socket_server;
        }
        foreach ($this->port_config as $value) {
            if ($value['socket_type'] == $type) {
                return $value;
            }
        }
    }
	
	/**
	 * 获取路由类
	 * @param type $server_port
	 * @return \Server\route_class_name|\Server\route_tool
	 * @throws \Exception
	 */
	protected function getRoute($server_port){
		if(isset($this->routes[$server_port])){
			return $this->routes[$server_port];
		}else{
			$route_tool = $this->port_config[$server_port]['route_tool'];
			if (class_exists($route_tool)) {
				$route = new $route_tool;
				$this->routes[$server_port] = $route;
				return $route;
			}
			$route_class_name = "Route\\" . $route_tool;
			if (class_exists($route_class_name)) {
				$route = new $route_class_name;
				$this->routes[$server_port] = $route;
			}else{
				throw new \Exception("class $route_tool is not exist.");
			}
			return $route;
		}
    }
	/**
	 * 获取封包类
	 * @param type $server_port
	 * @return JsonPack
	 * @throws \Exception
	 */
    protected function getPack($server_port){
		if(isset($this->packs[$server_port])){
			return $this->packs[$server_port];
		}else{
			$pack_tool = $this->port_config[$server_port]['pack_tool'];
			if (class_exists($pack_tool)) {
				$pack = new $pack_tool;
				$this->packs[$server_port] = $pack;
				return $pack;
			}
			$pack_class_name = "Pack\\" . $pack_tool;
			if (class_exists($pack_class_name)) {
				$pack = new $pack_class_name;
				$this->packs[$server_port] = $pack;
			} else {
				throw new \Exception("class $pack_tool is not exist.");
			}
			return $pack;
		}
    }
	
	
	
	
	
	/**
     * 获取workerId
     * @return int
     */
    public function getWorkerId(){
        return $this->workerId;
    }

    /**
     * 是不是worker进程
     * @param null $worker_id
     * @return bool
     */
    public function isWorker($worker_id = null){
        if ($worker_id == null) {
            $worker_id = $this->workerId;
        }
        return $worker_id < $this->worker_num ? true : false;
    }

    /**
     * 是否是task进程
     * @return bool
     */
    public function isTaskWorker(){
        return $this->server->taskworker ?? false;
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
     * 包装SerevrMessageBody消息
     * @param $type
     * @param $message
     * @param string $func
     * @return string
     */
    public function packServerMessageBody($type, $message, $func = null){
        $data['type'] = $type;
        $data['message'] = $message;
        $data['func'] = $func;
        return $data;
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
     * 发送消息给fd
     * @param $fd
     * @param $data
     */
    public function send($fd, $data){
        if (!$this->server->exist($fd)) {
            return null;
        }
		$server_port = $this->getServerPortByFd($fd);
		$server_info = $this->port_config[$server_port];
		
		$pack = $this->getPack($server_port);
		$pack_data = $pack->pack($data);
		if($server_info['socket_type']==self::socket_ws_server){
			return $this->server->push($fd, $pack_data);
		}elseif($server_info['socket_type']==self::socket_server){
			return $this->server->send($fd, $pack_data);
		}elseif($server_info['socket_type']==self::socket_http_server){
			return;
		}
    }
    /**
     * 发送消息给所有fd
     * @param $data
     */
    public function sendToAllFd($data){
		$send_data = $this->packServerMessageBody(SwooleMarco::MSG_TYPE_SEND_ALL_FD, ['data' => $data]);
        if ($this->isTaskWorker()) {
            $this->onSwooleTask($this->server, 0, 0, $send_data);
        }else{
            if ($this->task_num > 0) {
                $this->server->task($send_data);
            }else{
                foreach ($this->server->connections as $fd) {
                    $this->server->send($fd, $data, true);
                }
            }
        }
    }

    /**
     * 向uid发送消息
     * @param $uid
     * @param $data
     */
    public function sendToUid($uid, $data){
        if ($this->uid_fd_table->exist($uid)) {//本机处理
            $fd = $this->uid_fd_table->get($uid)['fd'];
            return $this->send($fd, $data, true);
		}else{
			return null;
		}
    }
	/**
     * 批量给UID发送消息
     * @param $uids uids
     * @param $data 消息
     */
    public function sendToUids($uids, $data){
        $current_fds = [];
        foreach ($uids as $key => $uid) {
            if ($this->uid_fd_table->exist($uid)) {
                $current_fds[] = $this->uid_fd_table->get($uid)['fd'];
                unset($uids[$key]);
            }
        }
        if (count($current_fds) > $this->send_use_task_num && $this->task_num > 0) {
			$send_data = $this->packServerMessageBody(SwooleMarco::MSG_TYPE_SEND_BATCH, ['data' => $data, 'fd' => $current_fds]);
            if ($this->isTaskWorker()) {
                $this->onSwooleTask($this->server, 0, 0, $send_data);
            }elseif($this->isWorker()) {
                $this->server->task($send_data);
            }else{
                foreach($current_fds as $fd) {
                    $this->send($fd, $data, true);
                }
            }
        }else{
            foreach($current_fds as $fd) {
                $this->send($fd, $data, true);
            }
        }
    }
	
	/**
     * 发送消息给所有Uid
     * @param $data 需要发送的数据
     */
    public function sendToAllUid($data){
        if ($this->isTaskWorker()) {
            $this->onSwooleTask($this->server, 0, 0, $data);
        } else {
            if ($this->task_num > 0) {
                $this->server->task($data);
            } else {
                foreach ($this->uid_fd_table as $row) {
                    $this->send($row['fd'], $data);
                }
            }
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