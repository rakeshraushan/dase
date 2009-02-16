<?php

class Dase_Config {

	private $conf = array();

	public function __construct()
	{
		$this->conf['app'] = array();
		$this->conf['auth'] = array();
		$this->conf['db'] = array();
		$this->conf['request_handler'] = array();
	}

	public function get($key)
	{
		if (isset($this->conf[$key])) {
			return $this->conf[$key];
		} else {
			return false;
		}
	}

	public function getAppSettings($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['app'][$setting])) {
				return $this->conf['app'][$setting];
			}
		} else {
			return $this->conf['app'];
		}
	}

	public function getAuth($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['auth'][$setting])) {
				return $this->conf['auth'][$setting];
			}
		} else {
			return $this->conf['auth'];
		}
	}

	public function getDb($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['db'][$setting])) {
				return $this->conf['db'][$setting];
			}
		} else {
			return $this->conf['db'];
		}
	}

	public function getCustomHandlers($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['request_handler'][$setting])) {
				return $this->conf['request_handler'][$setting];
			}
		} else {
			return $this->conf['request_handler'];
		}
	}

	public function getAll()
	{
		return $this->$conf;
	}

	public function set($key,$value)
	{
		$this->conf[$key] = $value;
	}

	public function load($conf_file)
	{
		if (file_exists($conf_file)) {
			$conf = $this->conf;
			include($conf_file);
			$this->conf = $conf;
		}
	}
}
