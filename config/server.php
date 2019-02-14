<?php
use Server\Swoole;
/**
 * server配置参数
 */
return [
	[
		'socket_type' => Swoole::socket_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'server',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9091,
		'pack_tool' => 'EofJsonPack',
		'route_tool' => 'NormalRoute',
		'max_connection' => 65535,
		'status' => 'start'
	],
	[
		'socket_type' => Swoole::socket_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'server',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9088,
		'pack_tool' => 'EofJsonPack',
		'route_tool' => 'NormalRoute',
		'max_connection' => 65535,
		'status' => 'start'
	],
	[
		'socket_type' => Swoole::socket_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'server',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9089,
		'pack_tool' => 'EofJsonPack',
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
		'ssl_cert_file' => __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.pem',
		'ssl_key_file' =>  __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.key',
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
		'ssl_cert_file' =>  __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.pem',
		'ssl_key_file' =>  __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.key',
		'status' => 'start'
	],
	[
		'socket_type' => Swoole::socket_http_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'http',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9092,
		'pack_tool' => 'NonJsonPack',
		'route_tool' => 'NormalRoute',
		'max_connection' => 65535,
		'ssl_cert_file' =>  __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.pem',
		'ssl_key_file' =>  __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.key',
		'status' => 'stop'
	],
	[
		'socket_type' => Swoole::socket_http_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'http',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9096,
		'pack_tool' => 'NonJsonPack',
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
		'pack_tool' => 'NonJsonPack',
		'route_tool' => 'NormalRoute',
		'max_connection' => 65535,
		'ssl_cert_file' =>  __DIR__ .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.pem',
		'ssl_key_file' =>  __DIR__ .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR.'ssl.key',
		'status' => 'start'
	],
	[
		'socket_type' => Swoole::socket_ws_server,
		'socket_protocol' => SWOOLE_SOCK_TCP,
		'name' => 'ws',
		'socket_name' => '0.0.0.0',
		'socket_port' => 9095,
		'pack_tool' => 'NonJsonPack',
		'route_tool' => 'NormalRoute',
		'max_connection' => 65535,
		'status' => 'start'
	]
];
