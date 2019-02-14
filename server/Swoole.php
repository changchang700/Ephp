<?php

namespace Server;

use Core\Config;
use Marco\SwooleMarco;

abstract class Swoole {

	/**
	 * tcp|udp类型
	 * socket_server
	 */
	const socket_server = 1;

	/**
	 * http类型
	 * socket_http_server
	 */
	const socket_http_server = 2;

	/**
	 * websocket类型
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
	 * worker_id
	 * @var type 
	 */
	public $worker_id;
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
	 * 此属性保存swoole_server实例
	 * swoole server 实例
	 * @var type 
	 */
	public $server = null;

	/**
	 * 是否开启进程守护
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
	 * 整个应用配置参数
	 * @var type 
	 */
	public $config;

	/**
	 * 监听端口配置参数
	 * @var type 
	 */
	public $port_config;

	/**
	 * 自定义路由
	 * @var type 
	 */
	public $routes = [];

	/**
	 * 自定义协议
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
		$this->name = $this->config['app']['name'];
		//设置worker_num
		$this->worker_num = $this->config['swoole']['worker_num'];
		//设置task_worker_num
        $this->task_num = $this->config['swoole']['task_worker_num'];
		//设置最大连接数
		$this->max_connection = $this->config['swoole']['max_connection'] ?? 102400;
		//设置是否开启进程守护
		$this->daemonize = $this->config['swoole']['daemonize'];
		//服务监听端口信息配置
		$port_config_before = $this->config['server'];
		foreach ($port_config_before as $value) {
			if ($value['socket_type'] == self::socket_ws_server) {
				$this->enable_swoole_websocket_erver = true;
			} elseif ($value['socket_type'] == self::socket_http_server) {
				$this->enable_swoole_http_erver = true;
			} else {
				$this->enable_swoole_tcp_server = true;
			}
			$port_config_after[$value['socket_port']] = $value;
		}
		$this->port_config = $port_config_after;
	}

	/**
	 * swoole启动之前操作
	 */
	protected function beforeSwooleStart() {
		//创建uid<->fd共享内存表
		$this->createUidTable();
	}

	/**
	 * 创建uid<->fd共享内存表
	 */
	protected function createUidTable() {
		$this->uid_fd_table = new \swoole_table($this->max_connection);
		$this->uid_fd_table->column('fd', \swoole_table::TYPE_INT, 8);
		$this->uid_fd_table->create();

		$this->fd_uid_table = new \swoole_table($this->max_connection);
		$this->fd_uid_table->column('uid', \swoole_table::TYPE_STRING, 32);
		$this->fd_uid_table->create();
	}

	/**
	 * 获取端口配置选项
	 * @param type $port 相应的端口
	 * @return type
	 */
	protected function getServerSet($port) {
		//公共的配置文件
		$set = $this->config['swoole'];
		//根据命令行设置的-d参数是否启动进程守护
		$set['daemonize'] = $this->daemonize;
		if ($port) {
			//获取自定义协议头配置参数
			$pack = $this->getPack($port);
			if ($pack == null) {
				$pack_set = [];
			}else{
				$pack_set = $pack->getPackSet();
			}
			//合并通用配置参数和协议头配置参数
			return array_merge($set, $pack_set);
		} else {
			return $set;
		}
	}

	/**
	 * 添加监听端口服务
	 * @param type $first_port 第一个监听的端口
	 * @throws \Exception
	 */
	protected function addServer($first_port) {
		foreach ($this->port_config as $value) {
			if ($value['socket_port'] == $first_port || $value['status'] == 'stop'){
				continue;
			}
			//获取配置参数
			$set = $this->getServerSet($value['socket_port']);

			//是否支持ssl
			$socket_ssl = false;
			if (array_key_exists('ssl_cert_file', $value) && array_key_exists('ssl_key_file', $value)) {
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
				if ($port == false) {
					throw new \Exception("{$value['socket_port']}端口创建失败");
				}
				//如果是http服务
				if ($value['socket_type'] == self::socket_http_server) {
					$set['open_http_protocol'] = true;

					$port->set($set);
					$port->on('Request', [$this, $value['request'] ?? 'onSwooleRequest']);
					$port->on('Handshake', function () {
						return false;
					});
				//如果是websocket服务
				} else {
					$set['open_http_protocol'] = true;
					$set['open_websocket_protocol'] = true;

					$port->set($set);
					$port->on('Open', [$this, $value['open'] ?? 'onSwooleOpen']);
					$port->on('Message', [$this, $value['message'] ?? 'onSwooleMessage']);
					$port->on('Handshake', [$this, $value['handshake'] ?? 'onSwooleHandShake']);
				}
			} else {
				if ($socket_ssl) {
					$port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol'] | SWOOLE_SSL);
				} else {
					$port = $this->server->listen($value['socket_name'], $value['socket_port'], $value['socket_protocol']);
				}
				if ($port == false) {
					throw new \Exception("{$value['socket_port']}端口创建失败");
				}

				$port->set($set);
				$port->on('Connect', [$this, $value['connect'] ?? 'onSwooleConnect']);
				$port->on('Receive', [$this, $value['receive'] ?? 'onSwooleReceive']);
				$port->on('Close', [$this, $value['close'] ?? 'onSwooleClose']);
				$port->on('Packet', [$this, $value['packet'] ?? 'onSwoolePacket']);
			}
		}
	}

	/**
	 * 获取第一个启动的服务
	 * 及其类型
	 * @return type
	 */
	protected function getFirstServer() {
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
	 * @return route class
	 * @throws \Exception
	 */
	protected function getRoute($server_port) {
		if (isset($this->routes[$server_port])) {
			return $this->routes[$server_port];
		} else {
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
			} else {
				throw new \Exception("class $route_tool is not exist.");
			}
			return $route;
		}
	}

	/**
	 * 获取封包类
	 * @param type $server_port
	 * @return pack class
	 * @throws \Exception
	 */
	protected function getPack($server_port) {
		if (isset($this->packs[$server_port])) {
			return $this->packs[$server_port];
		} else {
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
	 * 获取worker_id
	 * @return int
	 */
	public function getWorkerId() {
		return $this->worker_id;
	}

	/**
	 * 当前进程是否是worker进程
	 * @return bool
	 */
	public function isWorker() {
		return $this->server->taskworker?false:true;
	}

	/**
	 * 当前进程是否是task进程
	 * @return bool
	 */
	public function isTaskWorker() {
		return $this->server->taskworker?true:false;
	}

	/**
	 * 根据客户端标识获取socket型号
	 * @param type $fd 客户端标识
	 * @return type
	 */
	public function getSocketTypeByFd($fd) {
		return $this->port_config[$this->getServerPortByFd($fd)]['socket_type'];
	}

	/**
	 * 通过fd获取客户端连接信息详情
	 * @param $fd
	 * @return mixed
	 */
	public function getFdInfo($fd) {
		$fdinfo = $this->server->connection_info($fd);
		return $fdinfo;
	}

	/** 根据fd获取服务器端口
	 * @param $fd
	 * @return mixed
	 */
	public function getServerPortByFd($fd) {
		return $this->server->connection_info($fd)['server_port'];
	}

	/**
	 * 包装SerevrMessageBody消息
	 * @param $type
	 * @param $message
	 * @param string $func
	 * @return string
	 */
	public function packServerMessageBody($type, $message, $func = null) {
		$data['type'] = $type;
		$data['message'] = $message;
		$data['func'] = $func;
		return $data;
	}

	/**
	 * 发送消息给fd
	 * 所有发送都会调用次方法
	 * @param $fd
	 * @param $data
	 */
	public function send(int $fd, string $data) {
		if (!$this->server->exist($fd)) {
			return null;
		}
		//获取该连接对应端口参数
		$server_port = $this->getServerPortByFd($fd);
		$server_info = $this->port_config[$server_port];

		$pack = $this->getPack($server_port);
		$pack_data = $pack->pack($data);
		if ($server_info['socket_type'] == self::socket_ws_server) {
			return $this->server->push($fd, $pack_data);
		} elseif ($server_info['socket_type'] == self::socket_server) {
			return $this->server->send($fd, $pack_data);
		} elseif ($server_info['socket_type'] == self::socket_http_server) {
			return;
		}
	}

	/**
	 * 发送消息给所有fd
	 * @param $data 需要发送的消息
	 */
	public function sendToAllFd(string $data) {
		$send_data = $this->packServerMessageBody(SwooleMarco::MSG_TYPE_SEND_TO_ALL_FD, ['data' => $data]);
		if ($this->isTaskWorker()) {
			$this->onSwooleTask($this->server, 0, 0, $send_data);
		} else {
			//如果开启了异步任务则投递任务给异步进程执行，否则原生执行
			if ($this->task_num > 0) {
				$this->server->task($send_data);
			} else {
				foreach ($this->server->connections as $fd) {
					$this->send($fd, $data);
				}
			}
		}
	}

	/**
	 * 向uid发送消息
	 * @param $uid
	 * @param $data
	 */
	public function sendToUid(int $uid, string $data) {
		if ($this->uid_fd_table->exist($uid)) {//本机处理
			$fd = $this->uid_fd_table->get($uid)['fd'];
			return $this->send($fd, $data, true);
		} else {
			return null;
		}
	}

	/**
	 * 批量给uid发送消息
	 * @param $uids uids
	 * @param $data 消息
	 */
	public function sendToUids(array $uids, string $data) {
		$current_fds = [];
		foreach ($uids as $uid) {
			$current_fds[] = $this->getFdFromUid($uid);
		}
		if (count($current_fds) > $this->send_use_task_num && $this->task_num > 0) {
			$send_data = $this->packServerMessageBody(SwooleMarco::MSG_TYPE_SEND_TO_UIDS, ['fds' => $current_fds,'data' => $data]);
			if ($this->isTaskWorker()) {
				$this->onSwooleTask($this->server, 0, 0, $send_data);
			} elseif ($this->isWorker()) {
				$this->server->task($send_data);
			} else {
				foreach ($current_fds as $fd) {
					$this->send($fd, $data, true);
				}
			}
		} else {
			foreach ($current_fds as $fd) {
				$this->send($fd, $data, true);
			}
		}
	}

	/**
	 * 发送消息给所有Uid
	 * @param $data 需要发送的数据
	 */
	public function sendToAllUid(string $data) {
		$send_data = $this->packServerMessageBody(SwooleMarco::MSG_TYPE_SEND_TO_ALL_UID, ['data'=>$data]);
		if ($this->isTaskWorker()) {
			$this->onSwooleTask($this->server, 0, 0, $send_data);
		} else {
			if ($this->task_num > 0) {
				$this->server->task($send_data);
			} else {
				foreach ($this->uid_fd_table as $row) {
					$this->send($row['fd'], $data);
				}
			}
		}
	}

	/**
	 * 通过uid获取fd
	 * @param $uid
	 * @return mixed
	 */
	public function getFdFromUid($uid) {
		if($this->uid_fd_table->exist($uid)){
			return $this->uid_fd_table->get($uid, 'fd');
		}else{
			return null;
		}	
	}

	/**
	 * 通过fd获取uid
	 * @param $fd
	 * @return mixed
	 */
	public function getUidFromFd($fd) {
		if($this->fd_uid_table->exist($fd)){
			return $this->fd_uid_table->get($fd, 'uid');
		}else{
			return null;
		}
	}

	/**
	 * 将fd绑定到uid
	 * uid不能为0
	 * @param $fd fd
	 * @param $uid 用户id
	 */
	public function bindUid($fd, $uid) {
		$this->uid_fd_table->set($uid, ['fd' => $fd]);
		$this->fd_uid_table->set($fd, ['uid' => $uid]);
	}

	/**
	 * 解绑uid
	 * 链接断开自动解绑
	 * @param $uid 用户ID
	 */
	public function unBindUid($uid, $fd) {
		//更新共享内存
		$this->uid_fd_table->del($uid);
		$this->fd_uid_table->del($fd);
	}
    /**
     * 踢用户下线
     * @param $uid
     * @throws \Exception
     */
    public function kickUid($uid){
        $fd = $this->uid_fd_table->get($uid)['fd'];
		$this->close($fd);
    }
	/**
	 * 魔术方法
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		return call_user_func_array(array($this->server, $name), $arguments);
	}

}
