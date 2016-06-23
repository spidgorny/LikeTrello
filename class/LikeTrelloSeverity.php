<?php

class LikeTrelloSeverity extends LikeTrelloView {

	var $selfLink = 'LikeTrello/trello-severity';

	var $queryByPriority = [];

	var $countByPriority = [];

	function renderLists() {
		$content = '';
		$severity_codes = config_get('severity_enum_string');
		$t_status_array = MantisEnum::getAssocArrayIndexedByValues($severity_codes);
		$t_status_array = array_reverse($t_status_array, true);
		//pre_var_dump($severity_codes);
		//pre_var_dump($t_status_array);

		foreach ($t_status_array as $priority => $priorityCode) {
			$issues = $this->fetchIssuesBySeverity($priority);
			$issues = $this->renderIssuesWithColor($issues);

			$statusName = $this->getSeverityName($priority);
			$content .= '<div class="column">
				<div class="inside"
				id="'.$priority.'"
				style="background-color: white;"
				>
				<h2 title="'.$priority.'">' . $statusName .
				' <small>('.sizeof($issues).')</small></h2>';
			$content .= implode("\n", $issues);

//			$content .= $this->queryByPriority[$priority];
//			$content .= ': '.$this->countByPriority[$priority];
			$content .='</div>';  // inside
			$content .='</div>';  // column
		}
		return $content;
	}

	function fetchIssuesBySeverity($severity) {
		return $this->fetchIssues("severity = $severity");
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
		$t_bug_data->severity = gpc_get_int( 'to', $t_bug_data->severity );
		$t_bug_data->update( true, true );
		//header('Location: '.plugin_page('trello'));
		//$content = 'Status must be updated.';
		$content .= $this->renderLists();
		return $content;
	}

	function getSeverityName($status) {
		$element = get_enum_element('severity', $status);
		$name = string_display_line($element);
		return $name;
	}

}
