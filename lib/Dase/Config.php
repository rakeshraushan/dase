<?php

class Dase_Config {

	private $conf = array();

	public function __construct()
	{
		$this->conf['app'] = array();
		$this->conf['auth'] = array();
		$this->conf['db'] = array();
		$this->conf['handler'] = array();
	}

	public function setBasePath($base_path)
	{
		$this->conf['app']['base_path'] = $base_path;
	}

	public function get($key)
	{
		if (isset($this->conf[$key])) {
			return $this->conf[$key];
		} else {
			return false;
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
		} elseif (isset($this->conf['app']['base_path']) && 
			file_exists($this->conf['app']['base_path'].'/'.$conf_file)) {
				$conf = $this->conf;
				include($this->conf['app']['base_path'].'/'.$conf_file);
				$this->conf = $conf;
			}
	}
}
