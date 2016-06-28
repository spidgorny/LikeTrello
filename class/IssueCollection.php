<?php

class IssueCollection implements Iterator {

	var $where;

	var $count;

	var $issues;

	function __construct($where = '') {
		$this->where = $where;
	}

	function perform() {
		$t_project_id = helper_get_current_project();
		$t_bug_table = db_get_table('mantis_bug_table');
		$t_user_id = auth_get_current_user_id();
		$specific_where = helper_project_specific_where($t_project_id, $t_user_id);

		$query = "SELECT *
			FROM $t_bug_table
			WHERE $specific_where
			AND {$this->where}
			ORDER BY severity DESC, last_updated DESC
			LIMIT 20";
//		echo $query, BR; exit();
		$result = db_query_bound($query);
		$this->count = db_num_rows($result);

		$issues = [];
		for ($i = 0; $i < min($this->count, 10); $i++) {
			$row = db_fetch_array($result);
			$issues[$row['id']] = $row;
		}
		$this->issues = $issues;
	}

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		return current($this->issues);
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		next($this->issues);
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return key($this->issues);
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		return !!key($this->issues);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		reset($this->issues);
	}
}
