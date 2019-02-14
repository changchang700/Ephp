<?php

/**
 * app应用配置
 */
return [
	//响应事件
	'name' => 'Ephp', //应用名称
	'show_error_message' => true, //是否显示错误信息
	'event_controller' => 'event', //长连接类型事件响应控制器
	'event_connect_method' => 'onConnect', //长连接类型连接服务端事件响应方法
	'event_close_method' => 'onClose' //长连接类型断开连接事件响应方法
];
