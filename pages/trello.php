<?php

define('BR', "<br />\n");

function debug($a) {
	echo '<pre>';
	var_dump($a);
	echo '</pre>';
}

function ifsetor(&$variable, $default = null) {
	if (isset($variable)) {
		$tmp = $variable;
	} else {
		$tmp = $default;
	}
	return $tmp;
}

class LikeTrelloView {

	var $severity = NULL;

	var $handler = 0;

	function __construct() {
		//debug($_SESSION[__CLASS__]);
		$this->importParam('severity');
		$this->importParam('handler', 0);
	}

	function importParam($paramName, $default = NULL) {
		if (isset($_REQUEST[$paramName])) {	// isset to allow empty
			$this->$paramName = intval($_REQUEST[$paramName]);
			$_SESSION[__CLASS__][$paramName] = $this->$paramName;
		} else {
			$this->$paramName = ifsetor($_SESSION[__CLASS__][$paramName], $default);
		}
	}

	function render() {
		$result = $this->performAction();
		if ($result) {
			echo $result;
		} else {
			require 'trello.phtml';
		}
	}

	function renderLists() {
		$content = '';
		$status_codes = config_get('status_enum_string');
		$t_status_array = MantisEnum::getAssocArrayIndexedByValues($status_codes);
		//pre_var_dump($status_codes);
		//pre_var_dump($t_status_array);

		foreach ($t_status_array as $status => $statusCode) {
			$issues = $this->renderIssues($status);

			if (function_exists('html_get_status_css_class')) {
				$statusStyle = html_get_status_css_class($status,
					auth_get_current_user_id(),
					helper_get_current_project());
				$statusColor = '';
			} else {
				$statusStyle = '';
				$statusColor = get_status_color($status);
			}
			$statusName = $this->getStatusName($status);
			$content .= '<div class="column">
				<div class="inside ' . $statusStyle . '"
				style="background-color: '.$statusColor.'"
				id="'.$status.'">
				<h2 title="'.$status.'">' . $statusName . ' ('.sizeof($issues).')</h2>';
			$content .= implode("\n", $issues);
			$content .='</div>';  // inside
			$content .='</div>';  // column
		}
		return $content;
	}

	function renderIssues($status) {
		$content = array();
		$t_project_id = helper_get_current_project();
		$t_bug_table = db_get_table('mantis_bug_table');
		$t_user_id = auth_get_current_user_id();
		$specific_where = helper_project_specific_where($t_project_id, $t_user_id);
		if ($this->severity) {
			$severityCond = '= '.$this->severity;
		} else {
			$severityCond = '> -1';
		}
		if ($this->handler) {
			$handlerCond = '= '.$this->handler;
		} else {
			$handlerCond = '> -1';
		}

		$query = "SELECT *
			FROM $t_bug_table
			WHERE $specific_where
			AND status = $status
			AND severity $severityCond
			AND handler_id $handlerCond
			ORDER BY last_updated DESC
			LIMIT 20";
//		echo $query, BR; exit();
		$result = db_query_bound($query);
		$category_count = db_num_rows($result);
		for ($i = 0; $i < $category_count; $i++) {
			$row = db_fetch_array($result);
			//pre_var_dump($row);
			$content[] = '<div class="portlet ui-helper-clearfix" id="'.$row['id'].'">
			<div class="portlet-header">' .
				string_get_bug_view_link($row['id']) . ': ' .
				$row['summary'] . '</div>
			<div class="portlet-content">' .
				($row['reporter_id'] ? 'Reporter: ' . user_get_name($row['reporter_id']) . BR : '') .
				($row['handler_id'] ? 'Assigned: ' . user_get_name($row['handler_id']) . BR : '') .
				'</div></div>';
		}
		if ($row) {
			//pre_var_dump(array_keys($row));
		}
		return $content;
	}

	function performAction() {
		$content = NULL;
		if ($action = $_REQUEST['action']) {
			$method = $action.'Action';
			if (method_exists($this, $method)) {
				$content = $this->$method();
			}
		}
		return $content;
	}

	function moveAction() {
		$content = '';
		//debug($_REQUEST);
		$f_bug_id = gpc_get_int( 'issue' );
		$t_bug_data = bug_get( $f_bug_id, true );
		$t_bug_data->status	= gpc_get_int( 'to', $t_bug_data->status );
		$t_bug_data->update( true, true );
		//header('Location: '.plugin_page('trello'));
		//$content = 'Status must be updated.';
		$content .= $this->renderLists();
		return $content;
	}

	function getStatusName($status) {
		if (helper_get_current_project() == 20) {
			$map = array(
				10 => 'New (ideas)',
				20 => 'Active',
				50 => 'Less active',
				80 => 'Recently done',
				85 => 'Done',
				90 => 'Dead',
			);
			$name = $map[$status];
		} else {
			$element = get_enum_element('status', $status);
			$name = string_display_line($element);
		}
		return $name;
	}

}

$ltv = new LikeTrelloView();
$ltv->render();
