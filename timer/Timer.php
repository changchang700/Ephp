<?php

namespace Timer;

use Server\Server;

class Timer {

	public static $instance = null;
	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

	/**
	 * @return Timer
	 */
	public static function getInstance($config) {
		if (!self::$instance instanceof self) {
			self::$instance = new self($config);
		}
		return self::$instance;
	}

	public function tick($time, $func) {
		return Server::$application->server->tick($time, $func);
	}

	public function after($time, $func) {
		return Server::$application->server->after($time, $func);
	}

	public function clear($timer_id) {
		return Server::$application->server->clearTimer($timer_id);
	}

}
