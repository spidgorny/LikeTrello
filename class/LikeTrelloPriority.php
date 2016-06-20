<?php

class LikeTrelloPriority extends LikeTrelloView {

	var $selfLink = 'LikeTrello/trello-priority';

	var $queryByPriority = [];

	var $countByPriority = [];

	function renderLists() {
		$content = '';
		$priority_codes = config_get('priority_enum_string');
		$t_status_array = MantisEnum::getAssocArrayIndexedByValues($priority_codes);
		$t_status_array = array_reverse($t_status_array, true);
		//pre_var_dump($priority_codes);
		//pre_var_dump($t_status_array);

		foreach ($t_status_array as $priority => $priorityCode) {
			$issues = $this->fetchIssuesByPriority($priority);
			$issues = $this->renderIssuesWithColor($issues);

			$statusName = $this->getPriorityName($priority);
			$content .= '<div class="column">
				<div class="inside"
				id="'.$priority.'"
				style="background-color: white;"
				>
				<h2 title="'.$priority.'">' . $statusName .
				' <small>('.sizeof($issues).')</small></h2>';
			$content .= implode("\n", $issues);

			//$content .= $this->queryByPriority[$priority];
			//$content .= ': '.$this->countByPriority[$priority];
			$content .='</div>';  // inside
			$content .='</div>';  // column
		}
		return $content;
	}

	function fetchIssuesByPriority($priority) {
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
			AND priority = $priority
			AND severity $severityCond
			AND handler_id $handlerCond
			ORDER BY severity DESC, last_updated DESC
			LIMIT 20";
//		echo $query, BR; exit();
		$this->queryByPriority[$priority] = $query;
		$result = db_query($query);
		$category_count = db_num_rows($result);
		$this->countByPriority[$priority] = $category_count;

		$issues = [];
		for ($i = 0; $i < $category_count; $i++) {
			$row = db_fetch_array($result);
			$issues[$row['id']] = $row;
		}
		return $issues;
	}

	function renderIssuesWithColor(array $issues) {
		$content = array();
		foreach ($issues as $row) {
			$status = $row['status'];
			$statusStyle = html_get_status_css_class($status,
				auth_get_current_user_id(),
				helper_get_current_project());
			$statusColor = get_status_color($status);

			//pre_var_dump($row);
			$content[] = '<div 
			class="portlet ui-helper-clearfix '.$statusStyle.'" 
			style="background-color: '.$statusColor.'"
			id="'.$row['id'].'">
			<div class="portlet-header">' .
				string_get_bug_view_link($row['id']) . ': ' .
				htmlspecialchars($row['summary']).
			'</div>
			<div class="portlet-content">' .
				($row['reporter_id'] ? 'Reporter: ' . user_get_name($row['reporter_id']) . BR : '') .
				($row['handler_id'] ? 'Assigned: ' . user_get_name($row['handler_id']) . BR : '') .
				($row['severity'] ? 'Severity: ' . get_enum_element('severity', $row['severity']) . BR : '') .
				'</div>
			</div>';
		}
		return $content;
	}

	function moveAction() {
		$content = '';
		//debug($_REQUEST);
		$f_bug_id = gpc_get_int( 'issue' );
		/** @var BugData $t_bug_data */
		$t_bug_data = bug_get( $f_bug_id, true );
		$t_bug_data->priority = gpc_get_int( 'to', $t_bug_data->priority );
		$t_bug_data->update( true, true );
		//header('Location: '.plugin_page('trello'));
		//$content = 'Status must be updated.';
		$content .= $this->renderLists();
		return $content;
	}

	function getPriorityName($status) {
		$element = get_enum_element('priority', $status);
		$name = string_display_line($element);
		return $name;
	}

}
