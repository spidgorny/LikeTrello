<?php

class AppController {

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
			require __DIR__.'/../pages/trello.phtml';
		}
	}

	function performAction() {
		$content = NULL;
		if ($action = $_REQUEST['action']) {
			$method = $action.'Action';
			if (method_exists($this, $method)) {
				$r = new ReflectionMethod($this, $method);
				$params = $r->getParameters();
				if ($params) {
					$values = [];
					foreach ($params as $param) {
						$pName = $param->getName();
						$values[$pName] = ifsetor($_REQUEST[$pName]);
					}
					$content = call_user_func_array([$this, $method], $values);
				} else {
					$content = $this->$method();
				}
			}
		}
		return $content;
	}


}
