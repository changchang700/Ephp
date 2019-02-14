<?php

namespace Process;

use Process\AbstractProcess;
class TestProcess extends AbstractProcess {

	public function run($process) {
		echo "process is run.\n";
	}

	public function onShutDown() {
		echo "process is onShutDown.\n";
	}

	public function onReceive($str) {
		echo "process is onReceive.\n";
	}

}
