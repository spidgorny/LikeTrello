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
			$issuesContent = $this->renderIssuesWithColor($issues);

			$statusName = $this->getPriorityName($priority);
			$content .= '<div class="column">
				<div class="inside"
				id="'.$priority.'"
				style="background-color: white;"
				>
				<h2 title="'.$priority.'">' . $statusName .
				' <small>('.$issues->count.')</small></h2>';
			$content .= implode("\n", $issuesContent);

			//$content .= $this->queryByPriority[$priority];
			//$content .= ': '.$this->countByPriority[$priority];
			$content .='</div>';  // inside
			$content .='</div>';  // column
		}
		return $content;
	}

	function fetchIssuesByPriority($priority) {
		return $this->fetchIssues("priority = $priority");
	}

	/**
	 * @param $issues IssueCollection|array
	 * @return array
	 */
	function renderIssuesWithColor($issues) {
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
