<?php
use Server\Swoole;
return [
	'name'=>'Ephp', //应用名称
	'show_error_message' => true,
	'set' => [
		'daemonize' => 0, //是否进程守护
		'worker_num' => 4, //worker process num
		'open_tcp_nodelay' => 1,
		'dispatch_mode' => 2,
		'task_worker_num' => 5,
		'task_max_request' => 5000,
		'enable_reuse_port' => true,
		'heartbeat_idle_time' => 120, //2分钟后没消息自动释放连接
		'heartbeat_check_interval' => 60, //1分钟检测一次
		'max_connection' => 100000,
		'document_root' => __DIR__.'/../Views/',
		'enable_static_handler' => true,
		'ssl_cert_file' => __DIR__.'/ssl.pem',
		'ssl_key_file' => __DIR__.'/ssl.key'
	],
	'server' => [
		[
			'socket_type' => Swoole::socket_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'server',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9091,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'server',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9099,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'server',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9094,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_http_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'http',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9092,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_http_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'http',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9096,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_ws_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'ws',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9093,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_ws_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'ws',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9095,
			'pack_tool' => 'JsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'status' => 'start'
		]
	]
];
