<?php

//echo __FILE__, ':', __LINE__, '<br />', PHP_EOL;

class LikeTrelloPlugin extends MantisPlugin {

	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register( ) {
		$this->name = 'Like Trello';
		$this->description = 'Change status of issues by dragging them';
		$this->page = '';

		$this->version = '1.0c';
		$this->requires = array(
			'MantisCore' => '2.0',
		);

		$this->author = 'Slawa';
		$this->contact = 'spidgorny@gmail.com';
		$this->url = 'https://github.com/spidgorny/LikeTrello';
	}

	function events() {
		//echo __METHOD__, '<br />', PHP_EOL;
		return array(
			'EVENT_MENU_MAIN_FRONT' => EVENT_TYPE_DEFAULT,
		);
	}

	function hooks() {
		//echo __METHOD__, '<br />', PHP_EOL;
		return array(
			'EVENT_MENU_MAIN' => 'addMenu',
		);
	}

	function config() {
		return array(
			'foo_or_bar' => 'foo',
		);
	}

	function addMenu($event_name, $param) {
		//echo __METHOD__, '<br />', PHP_EOL;
		if (MANTIS_VERSION >= '2.0') {
			$s_plugin_like_trello_title = plugin_lang_get( 'title' );
			$s_plugin_like_trello_priority = plugin_lang_get( 'priority' );
			$s_plugin_like_trello_severity = plugin_lang_get( 'severity' );
			return [
				[
					'url' => plugin_page('trello', true),
					'title' => $s_plugin_like_trello_title,
					'icon' => 'fa-trello',
				],
				[
					'url' => plugin_page('trello-priority', true),
					'title' => $s_plugin_like_trello_priority,
					'icon' => 'fa-trello',
				],
				[
					'url' => plugin_page('trello-severity', true),
					'title' => $s_plugin_like_trello_severity,
					'icon' => 'fa-trello',
				],
			];
		} else {
			return '<a href="' .
			plugin_page('trello') .
			'">Like Trello</a>';
		}
	}

}
