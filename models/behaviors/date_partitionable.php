<?php
/**
 * Behavior for CakePHP supports MySQL partitioning by date.
 *
 * @author	deeeki <deeeki@gmail.com>
 */
class DatePartitionableBehavior extends ModelBehavior {
	public function createPartition(&$Model, $field, $date) {
		$ts = strtotime($date);
		if ($ts === false) { return false; }

		$partition_name = $this->_getPartitionName($ts);
		$partition_date = $this->_getPartitionDate($ts);

		$sql = "ALTER TABLE {$Model->table} PARTITION BY RANGE (TO_DAYS({$field})) ";
		$sql .= "(PARTITION {$partition_name} VALUES LESS THAN (TO_DAYS('{$partition_date}')) COMMENT = '{$partition_date}')";

		return $Model->query($sql);
	}

	public function removePartition(&$Model) {
		$sql = "ALTER TABLE {$Model->table} REMOVE PARTITIONING";

		return $Model->query($sql);
	}

	public function addPartition(&$Model, $date) {
		$ts = strtotime($date);
		if ($ts === false) { return false; }

		$partition_name = $this->_getPartitionName($ts);
		$partition_date = $this->_getPartitionDate($ts);

		$sql = "ALTER TABLE {$Model->table} ADD PARTITION ";
		$sql .= "(PARTITION {$partition_name} VALUES LESS THAN (TO_DAYS('{$partition_date}')) COMMENT = '{$partition_date}')";

		return $Model->query($sql);
	}

	public function dropPartition(&$Model, $date) {
		$ts = strtotime($date);
		if ($ts === false) { return false; }

		$partition_name = $this->_getPartitionName($ts);

		$sql = "ALTER TABLE {$Model->table} DROP PARTITION {$partition_name}";

		return $Model->query($sql);
	}

	private function _getPartitionName($ts) {
		return 'p' . date('Ymd', $ts);
	}

	private function _getPartitionDate($ts) {
		return date('Y-m-d', $ts + 86400) . ' 00:00:00';
	}
}
