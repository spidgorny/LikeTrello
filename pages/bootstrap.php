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

function pre_var_dump($a) {
	echo '<pre>';
	var_dump($a);
	echo '</pre>';
}

require_once __DIR__.'/../class/AppController.php';
require_once __DIR__.'/../class/LikeTrelloView.php';
require_once __DIR__.'/../class/LikeTrelloPriority.php';
require_once __DIR__.'/../class/LikeTrelloSeverity.php';
require_once __DIR__.'/../class/IssueCollection.php';
