<?php
namespace Core;

use Core\Config;
use Server\SwooleServer;
class ServerManger{
	const SOCK_TCP = 1;
    const SOCK_HTTP = 10;
    const SOCK_WS = 11;
	
	public $enable_swoole_tcp_server = false;
	public $enable_swoole_http_erver = false;
	public $enable_swoole_websocket_erver = false;
	
	public $config;
	
	public function __construct() {
		$this->config = (new Config())->get('server');
		foreach ($this->config as $value) {
            if ($value['socket_type'] == self::SOCK_WS) {
                $this->enable_swoole_websocket_erver = true;
            } else if ($value['socket_type'] == self::SOCK_HTTP) {
                $this->enable_swoole_http_erver = true;
            } else {
                $this->enable_swoole_tcp_server = true;
            }
        }
	}
	
	/**
	 * 监听端口
	 * @param SwooleServer $swoole_server
	 * @param type $first_port
	 * @throws \Exception
	 */
    public function addServer(SwooleServer $swoole_server,$first_port){
        foreach ($this->config as $key => $value) {
            if ($value['socket_port'] == $first_port) continue;
			$set = array();
            if ($value['socket_type'] == self::SOCK_HTTP || $value['socket_type'] == self::SOCK_WS) {
                $port = $swoole_server->server->listen($value['socket_name'], $value['socket_port'], self::SOCK_TCP);
				
                if ($port == false) {
                    throw new \Exception("{$value['socket_port']}端口创建失败");
                }
                if ($value['socket_type'] == self::SOCK_HTTP) {
                    $set['open_http_protocol'] = true;
                    $port->set($set);
                    $port->on('request', [$swoole_server, $value['request'] ?? 'onSwooleRequest']);
                    $port->on('handshake', function () {
                        return false;
                    });
                } else {
                    $set['open_http_protocol'] = true;
                    $set['open_websocket_protocol'] = true;
                    $port->set($set);
                    $port->on('open', [$swoole_server, $value['open'] ?? 'onSwooleWSOpen']);
                    $port->on('message', [$swoole_server, $value['message'] ?? 'onSwooleWSMessage']);
                    $port->on('close', [$swoole_server, $value['close'] ?? 'onSwooleWSClose']);
                    $port->on('handshake', [$swoole_server, $value['handshake'] ?? 'onSwooleWSHandShake']);
                }
            } else {
                $port = $swoole_server->server->listen($value['socket_name'], $value['socket_port'], $value['socket_type']);
                if ($port == false) {
                    throw new \Exception("{$value['socket_port']}端口创建失败");
                }
                $port->set($set);
                $port->on('connect', [$swoole_server, $value['connect'] ?? 'onSwooleConnect']);
                $port->on('receive', [$swoole_server, $value['receive'] ?? 'onSwooleReceive']);
                $port->on('close', [$swoole_server, $value['close'] ?? 'onSwooleClose']);
                $port->on('packet', [$swoole_server, $value['packet'] ?? 'onSwoolePacket']);
            }
        }
    }
	
    public function getFirstServer(){
        if ($this->enable_swoole_websocket_erver) {
            $type = self::SOCK_WS;
        } else if ($this->enable_swoole_http_erver) {
            $type = self::SOCK_HTTP;
        } else {
            $type = self::SOCK_TCP;
        }
        foreach ($this->config as $value) {
            if ($value['socket_type'] == $type) {
                return $value;
            }
        }
        return $this->config[0];
    }
}

