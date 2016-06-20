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

require_once 'LikeTrelloView.php';
require_once 'LikeTrelloPriority.php';
require_once 'LikeTrelloSeverity.php';
