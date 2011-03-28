<?php
/**
 * DatePartitionableBehavior sample shell.
 *
 * @author	deeeki <deeeki@gmail.com>
 */
class PartitionShell extends Shell {
	public $uses = array('History'); //input partitionable target model name on first.
	public $tasks = array();

	public function startup() {
		$this->out(date('[Y-m-d H:i:s]') . $this->alias . ' start.');
		Configure::write('debug', 2);

		$this->{$this->modelClass}->Behaviors->attach('DatePartitionable');
	}

	public function main() {
		$this->err('[ERROR] use any commands : create/remove/add/add_bulk/drop/drop_bulk');
	}

	public function create() {
		$count = (isset($this->args[0])) ? intval($this->args[0]) : 1;

		$this->{$this->modelClass}->createPartition('created', date('Ymd'));
		for ($i = 1; $i < $count; $i++) {
			$this->{$this->modelClass}->addPartition(date('Ymd', strtotime('+' . $i . ' days')));
		}
		$this->_shutdown();
	}

	public function remove() {
		$this->{$this->modelClass}->removePartition();
		$this->_shutdown();
	}

	public function add() {
		if (!isset($this->args[0])) {
			$this->err("[ERROR] require date parameter. ex: 'add 20110101'");
			return;
		}
		$this->{$this->modelClass}->addPartition($this->args[0]);
		$this->_shutdown();
	}

	public function add_bulk() {
		for ($i = 0; $i < 7; $i++) {
			$day = $i + 7;
			$ts = strtotime('+' . $day . ' days');
			$this->{$this->modelClass}->addPartition(date('Ymd', $ts));
		}
		$this->_shutdown();
	}

	public function drop() {
		if (!isset($this->args[0])) {
			$this->err("[ERROR] require date parameter. ex: 'drop 20110101'");
			return;
		}
		$this->{$this->modelClass}->dropPartition($this->args[0]);
		$this->_shutdown();
	}

	public function drop_bulk() {
		for ($i = 0; $i < 7; $i++) {
			$day = $i + 7;
			$ts = strtotime('-' . $day . ' days');
			$this->{$this->modelClass}->dropPartition(date('Ymd', $ts));
		}
		$this->_shutdown();
	}

	private function _shutdown() {
		App::import('Model', 'ConnectionManager');
		$db =& ConnectionManager::getDataSource('default');

		$querylogs = $db->getLog();
		foreach ($querylogs['log'] as $log) {
			$this->out($log['query']);
		}

		$this->out(date('[Y-m-d H:i:s]') . $this->alias . ' end.');
	}
}
