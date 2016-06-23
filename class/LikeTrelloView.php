<?php

class LikeTrelloView extends AppController {

	var $selfLink = 'LikeTrello/trello';

	var $severity = NULL;

	var $handler = 0;

	var $status = [];

	function __construct() {
		//debug($_SESSION[__CLASS__]);
		$this->importParam('severity');
		$this->importParam('handler', 0);
		$this->importParam('status', []);
	}

	function getStatuses() {
		$status_codes = config_get('status_enum_string');
		$t_status_array = MantisEnum::getAssocArrayIndexedByValues($status_codes);
		return $t_status_array;
	}

	function renderLists() {
		$content = '';
		$t_status_array = $this->getStatuses();
		//pre_var_dump($status_codes);
		//pre_var_dump($t_status_array);

		foreach ($t_status_array as $status => $statusCode) {
			$issues = $this->fetchIssuesByStatus($status);
			$issues = $this->renderIssues($issues);
			$statusName = $this->getStatusName($status);

			$content .= '<div class="column">
				<div class="inside ' . $this->getStatusClass($status) . '"
				style="background-color: '.$this->getStatusColor($status).'"
				id="'.$status.'">
				<h2 title="'.$status.'">' . $statusName . ' ('.sizeof($issues).')</h2>';
			$content .= implode("\n", $issues);
			$content .='</div>';  // inside
			$content .='</div>';  // column
		}
		return $content;
	}

	function getStatusClass($status) {
		$statusStyle = '';
		if (function_exists('html_get_status_css_class')) {
			$statusStyle = html_get_status_css_class($status,
				auth_get_current_user_id(),
				helper_get_current_project());
		}
		return $statusStyle;
	}

	function getStatusColor($status) {
		$statusColor = get_status_color($status);
		return $statusColor;
	}

	function fetchIssues($where) {
		$t_project_id = helper_get_current_project();
		$t_bug_table = db_get_table('mantis_bug_table');
		$t_user_id = auth_get_current_user_id();
		$specific_where = helper_project_specific_where($t_project_id, $t_user_id);

		if ($this->severity) {
			$severityCond = '= ' . $this->severity;
		} else {
			$severityCond = '> -1';
		}

		if ($this->handler) {
			$handlerCond = '= ' . $this->handler;
		} else {
			$handlerCond = '> -1';
		}

		if ($this->status) {
			$where .= ' AND status IN (' . implode(', ', $this->status).')';
		}

		$query = "SELECT *
			FROM $t_bug_table
			WHERE $specific_where
			AND $where
			AND severity $severityCond
			AND handler_id $handlerCond
			ORDER BY severity DESC, last_updated DESC
			LIMIT 20";
//		echo $query, BR; exit();
		$result = db_query_bound($query);
		$category_count = db_num_rows($result);

		$issues = [];
		for ($i = 0; $i < $category_count; $i++) {
			$row = db_fetch_array($result);
			$issues[$row['id']] = $row;
		}
		return $issues;
	}

	function fetchIssuesByStatus($status) {
		return $this->fetchIssues("status = $status");
	}

	function renderIssues(array $issues) {
		$content = array();
		foreach ($issues as $row) {
			//pre_var_dump($row);
			$content[] = '<div class="portlet ui-helper-clearfix" id="'.$row['id'].'">
			<div class="portlet-header">' .
				string_get_bug_view_link($row['id']) . ': ' .
				htmlspecialchars($row['summary']) .
			'</div>
			<div class="portlet-content">' .
				($row['reporter_id'] ? 'Reporter: ' . user_get_name($row['reporter_id']) . BR : '') .
				($row['handler_id'] ? 'Assigned: ' . user_get_name($row['handler_id']) . BR : '') .
				($row['severity'] ? 'Severity: ' . get_enum_element('severity', $row['severity']) . BR : '') .
				'</div></div>';
		}
		return $content;
	}

	function moveAction() {
		$content = '';
		//debug($_REQUEST);
		$f_bug_id = gpc_get_int( 'issue' );
		/** @var BugData $t_bug_data */
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

	function print_enum_string_option_list_with_count( $p_enum_name, $p_val = 0 ) {
		$t_config_var_name = $p_enum_name . '_enum_string';
		$t_config_var_value = config_get( $t_config_var_name );

		if( is_array( $p_val ) ) {
			$t_val = $p_val;
		} else {
			$t_val = (int)$p_val;
		}

		$t_enum_values = MantisEnum::getValues( $t_config_var_value );

		foreach ( $t_enum_values as $t_key ) {
			$t_elem2 = get_enum_element( $p_enum_name, $t_key );

			$t_elem2 .= ' ('.$this->getCountBySeverity($t_key).')';

			echo '<option value="' . $t_key . '"';
			check_selected( $t_val, $t_key );
			echo '>' . string_html_specialchars( $t_elem2 ) . '</option>';
		}
	}

	function getCountBySeverity($severity) {
		$t_project_id = helper_get_current_project();
		$t_bug_table = db_get_table('mantis_bug_table');
		$t_user_id = auth_get_current_user_id();
		$specific_where = helper_project_specific_where($t_project_id, $t_user_id);
		$closed = CLOSED;
		$resolved = RESOLVED;
		if ($this->handler) {
			$handlerCond = '= '.$this->handler;
		} else {
			$handlerCond = '> -1';
		}
		$query = "SELECT *
			FROM $t_bug_table
			WHERE $specific_where
			AND severity = $severity
			AND status != $closed
			AND status != $resolved
			AND handler_id $handlerCond
			";
		//echo $query, BR;
// 		exit();
		$result = db_query_bound($query);
		$category_count = db_num_rows($result);
		return $category_count;
	}

	function addIssueAction($summary) {
		$t_bug_data = new BugData;
		$t_bug_data->project_id = helper_get_current_project();
		$t_bug_data->reporter_id = auth_get_current_user_id();
		$t_bug_data->category_id = 36;
		$t_bug_data->summary = $summary;
		if (strlen($summary) <= 128) {
			$t_bug_data->description = '.';
		} else {
			$t_bug_data->description = $summary;
		}
		$id = $t_bug_data->create();
		if ($id) {
			html_meta_redirect('plugin.php?page='.$this->selfLink);
			exit();
		}
	}

}
