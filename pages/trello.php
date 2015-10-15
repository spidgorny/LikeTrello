<?php

define('BR', "<br />\n");
function debug($a) {
	echo '<pre>';
	var_dump($a);
	echo '</pre>';
}

class LikeTrelloView {

	var $severity = NULL;

	function __construct() {
		if (isset($_REQUEST['severity'])) {	// isset to allow empty
			$this->severity = intval($_REQUEST['severity']);
			$_SESSION[__CLASS__]['severity'] = $this->severity;
		} else {
			$this->severity = $_SESSION[__CLASS__]['severity'];
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
			$statusName = string_display_line( get_enum_element( 'status', $status ) );
			$content .= '<div class="column">
				<div class="inside"
				style="background-color: '.get_status_color($status).'"
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

		$query = "SELECT *
			FROM $t_bug_table
			WHERE $specific_where
			AND status = $status
			AND severity $severityCond
			ORDER BY last_updated DESC
			LIMIT 20";
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

}

$ltv = new LikeTrelloView();
$ltv->render();
