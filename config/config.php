<?php
use Server\Swoole;
return [
	'name'=>'Ephp', //应用名称
	'show_error_message' => true,
	'set' => [
		'daemonize' => 0,	//是否进程守护
		'worker_num' => 4,	//worker process num
		'open_tcp_nodelay' => 1,	//启用open_tcp_nodelay，开启后TCP连接发送数据时会关闭Nagle合并算法，立即发往客户端连接。在某些场景下，如http服务器，可以提升响应速度。
		'dispatch_mode' => 2,	//数据包分发策略。可以选择3种类型，默认为2
		'task_worker_num' => 5,	//异步任务进程数量
		'task_max_request' => 5000,	//异步任务最大请求数量
		'enable_reuse_port' => true,	//设置端口重用，此参数用于优化TCP连接的Accept性能，启用端口重用后多个进程可以同时进行Accept操作。
		'heartbeat_idle_time' => 120,	//2分钟后没消息自动释放连接
		'heartbeat_check_interval' => 60,	//1分钟检测一次
		'max_connection' => 100000,	//最大连接数
		'max_request' => 100,	//设置worker进程的最大任务数
		'document_root' => __DIR__.'/../Views/', //web目录
		'enable_static_handler' => true	//设置静态文件访问
	],
	'server' => [
		[
			'socket_type' => Swoole::socket_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'server',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9091,
			'pack_tool' => 'LenJsonPack',
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
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'ssl_cert_file' => __DIR__.'/ssl.pem',
			'ssl_key_file' => __DIR__.'/ssl.key',
			'status' => 'start'
		],
		[
			'socket_type' => Swoole::socket_server,
			'socket_protocol' => SWOOLE_SOCK_TCP,
			'name' => 'server',
			'socket_name' => '0.0.0.0',
			'socket_port' => 9094,
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535,
			'ssl_cert_file' => __DIR__.'/ssl.pem',
			'ssl_key_file' => __DIR__.'/ssl.key',
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
			'ssl_cert_file' => __DIR__.'/ssl.pem',
			'ssl_key_file' => __DIR__.'/ssl.key',
			'status' => 'stop'
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
			'ssl_cert_file' => __DIR__.'/ssl.pem',
			'ssl_key_file' => __DIR__.'/ssl.key',
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
	],
	'event_controller' => 'event',
	'event_connect_method' => 'onConnect',
	'event_close_method' => 'onClose'
];
