<?php
use Core\ServerManger;
return [
	'name'=>'Ephp', //应用名称
	'set' => [
		'daemonize' => 0, //是否进程守护
		'worker_num' => 4,    //worker process num
		'open_tcp_nodelay' => 1,
		'dispatch_mode' => 2,
		'task_worker_num' => 5,
		'task_max_request' => 5000,
		'enable_reuse_port' => true,
		'heartbeat_idle_time' => 120,//2分钟后没消息自动释放连接
		'heartbeat_check_interval' => 60,//1分钟检测一次
		'max_connection' => 100000,
		'document_root' => __DIR__.'/../Views/',
		'enable_static_handler' => true
	],
	'server' => [
		[
			'socket_type' => ServerManger::SOCK_TCP,
			'socket_name' => '0.0.0.0',
			'socket_port' => 9091,
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535
		],
		[
			'socket_type' => ServerManger::SOCK_TCP,
			'socket_name' => '0.0.0.0',
			'socket_port' => 9094,
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535
		],
		[
			'socket_type' => ServerManger::SOCK_HTTP,
			'socket_name' => '0.0.0.0',
			'socket_port' => 9092,
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535
		],
		[
			'socket_type' => ServerManger::SOCK_WS,
			'socket_name' => '0.0.0.0',
			'socket_port' => 9093,
			'pack_tool' => 'LenJsonPack',
			'route_tool' => 'NormalRoute',
			'max_connection' => 65535
		]
	]
];
