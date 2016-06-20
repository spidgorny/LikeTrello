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
		$this->url = '';
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
			return plugin_page('trello', true);
		} else {
			return '<a href="' .
			plugin_page('trello') .
			'">Like Trello</a>';
		}
	}

}
