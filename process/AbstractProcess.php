<?php

namespace Process;

use Swoole\Process;

abstract class AbstractProcess {

	private $swooleProcess;
	private $processName;
	private $async = null;

	/**
	 * 设置进程初始化参数
	 * @param string $processName 进程名称
	 * @param array $args 进程参数
	 * @param type $async 是否异步
	 */
	final function __construct($processName, $args = [], $async = true) {
		$this->async = $async;
		$this->args = $args;
		$this->processName = $processName;
		$this->swooleProcess = new \swoole_process([$this, '__start']);
	}

	public function getProcess() {
		return $this->swooleProcess;
	}

	/*
	 * 服务启动后才能获得到pid
	 */

	public function getPid() {
		if (isset($this->swooleProcess->pid)) {
			return $this->swooleProcess->pid;
		} else {
			return null;
		}
	}

	function __start($process) {
		if (PHP_OS != 'Darwin') {
			$process->name($this->getProcessName());
		}
		if (extension_loaded('pcntl')) {
			pcntl_async_signals(true);
		}
		//进程退出新号，触发退出事件（强制退出不会触发）
		Process::signal(SIGTERM, function ()use($process) {
			$this->onShutDown();
			swoole_event_del($process->pipe);
			$this->swooleProcess->exit(0);
		});
		//进程收到管道信息事件
		if ($this->async) {
			swoole_event_add($this->swooleProcess->pipe, function() {
				$msg = $this->swooleProcess->read(64 * 1024);
				$this->onReceive($msg);
			});
		}
		$this->run($this->swooleProcess);
	}

	public function getProcessName() {
		return $this->processName;
	}

	public abstract function run($process);

	public abstract function onShutDown();

	public abstract function onReceive($str);
}
