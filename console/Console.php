<?php
namespace Console;

use Server\Server;
use Server\SwooleServer;
class Console{
	/**
	 * 界面宽度
	 * @var type 
	 */
	const lengh = 95;
	/**
	 * 填充数量
	 */
	const title_pad = 10;
	/**
	 * 填充数量
	 */
	const data_pad = 14;
	/**
	 * 标题提示
	 * @var type 
	 */
	public static $title = "Welcome to use this software";
	
	/**
	 * 端口提示
	 * @var type 
	 */
	public static $info = "Port information";
	/**
	 * 启动成功之后的提示
	 * @var type 
	 */
	public static $after_start_info = "Service started successfully";

	/**
	 * 端口界面渲染
	 */
	public static function gui(){
		//UI界面显示
		echo str_repeat(" ",self::lengh)."\n";
		echo str_repeat("-", (self::lengh-strlen(self::$title))/2).self::colorFont(self::$title,44,37).str_repeat("-", (self::lengh-strlen(self::$title))/2)."\n";
		echo str_repeat("=",self::lengh)."\n";
		echo "Swoole Version:".self::colorFont(swoole_version(),34).
			 " PHP Version:".self::colorFont(phpversion(),34).
			 " OS Version:".self::colorFont(PHP_OS ,34).
			 " APP Version:".self::colorFont(SwooleServer::version,34)."\n";
		echo str_repeat("=",self::lengh)."\n";
		echo str_repeat("-", self::lengh)."\n";
		echo str_repeat(" ", (self::lengh-strlen(self::$info))/2).self::colorFont(self::$info,35).str_repeat(" ", (self::lengh-strlen(self::$info))/2)."\n";
		echo str_repeat("-", self::lengh)."\n";
		echo self::colorFont("TYPE",34).str_repeat(" ",self::title_pad).
			 self::colorFont("ADDRSS",34).str_repeat(" ",self::title_pad).
			 self::colorFont("PORT",34).str_repeat(" ",self::title_pad).
			 self::colorFont("SSL",34).str_repeat(" ",self::title_pad).
			 self::colorFont("PACK",34).str_repeat(" ",self::title_pad).
			 self::colorFont("ROUTE",34).str_repeat(" ",self::title_pad).
			 self::colorFont("STATUS",34)."\n";
		echo str_repeat("-", self::lengh)."\n";
		foreach (Server::$application->config['server'] as $value) {
			echo $value['name']. str_repeat(" ", self::data_pad- strlen($value['name'])).
				 $value['socket_name'].str_repeat(" ", self::data_pad+2- strlen($value['socket_name'])).
				 $value['socket_port'].str_repeat(" ", self::data_pad- strlen($value['socket_port'])).
				 (isset($value['ssl_cert_file'])?self::colorFont("true ",32):self::colorFont("false",31)).str_repeat(" ", self::data_pad-6).
				 $value['pack_tool'].str_repeat(" ", self::data_pad- strlen($value['pack_tool'])).
				 $value['route_tool'].str_repeat(" ", self::data_pad+1- strlen($value['route_tool'])).
				 ($value['status']=='start'?self::colorFont("[{$value['status']}]",32):self::colorFont("[{$value['status']}]",31))."\n";
		}
		echo str_repeat("-", self::lengh)."\n";
		echo str_repeat(" ", (self::lengh-strlen(self::$after_start_info))/2).self::colorFont(self::$after_start_info,32).str_repeat(" ", (self::lengh-strlen(self::$after_start_info))/2)."\n";
		echo str_repeat("-", self::lengh)."\n";
	}
	
	/**
	 * 展示帮助命令行
	 */
	public static function help(){
		echo self::colorFont("usage:",33)."\n";
		echo self::colorFont("    command [options] [arguments]",32)."\n";
		echo self::colorFont("command:",33)."\n";
		echo self::colorFont("    start    Start server",32)."\n";
		echo self::colorFont("    stop     Stop server",32)."\n";
		echo self::colorFont("options:",33)."\n";
		echo self::colorFont("    -d    Daemon",32)."\n";
	}
	
	/**
	 * 操作成功提示
	 * @param type $msg 提示信息
	 */
	public static function success($msg){
		echo self::colorFont(str_repeat(" ", self::lengh),42,37)."\n";
		echo self::colorFont(str_repeat(" ", (self::lengh-strlen($msg)+2)/2),42,37).self::colorFont($msg,42,37).self::colorFont(str_repeat(" ", (self::lengh-strlen($msg))/2),42,37)."\n";
		echo self::colorFont(str_repeat(" ", self::lengh),42,37)."\n";
	}
	
	/**
	 * 错误提示
	 * @param type $msg 提示信息
	 */
	public static function error($msg){
		echo self::colorFont(str_repeat(" ", self::lengh),41,37)."\n";
		echo self::colorFont(str_repeat(" ", (self::lengh-strlen($msg)+2)/2),41,37).self::colorFont($msg,41,37).self::colorFont(str_repeat(" ", (self::lengh-strlen($msg))/2),41,37)."\n";
		echo self::colorFont(str_repeat(" ", self::lengh),41,37)."\n";
	}
	
	/**
	 * 警告样式
	 * @param type $msg 提示信息
	 */
	public static function warning($msg){
		echo self::colorFont("[".date("Y-m-d H:i:s")."][warning]:".$msg,33)."\n";
	}

	/**
	 * 控制台颜色输出
	 * @param type $data 需要格式化的字符串
	 * @param type $font_color_code 字体颜色
	 * @param type $background_color_code 背景颜色
	 * @return type 返回格式化的字符串
	 */
	private static function colorFont($data,$font_color_code=40,$background_color_code=40){
		return "\033[{$font_color_code};{$background_color_code}m{$data}\033[0m";
	}
}

