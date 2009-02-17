<?php

class Dase_Config_Exception extends Exception {}

class Dase_Config {

	private $conf = array();

	public function __construct($base_dir)
	{
		$this->base_dir = $base_dir;
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

	public function getCacheType()
	{
		if (isset($this->conf['app']['cache_type'])) {
			return $this->conf['app']['cache_type'];
		}
	}

	public function getCacheDir()
	{
		$cache_base_dir = $this->getAppSettings('cache_base_dir');
		if (!$cache_base_dir) {
			throw new Dase_Cache_Exception('no cache_base_dir defined');
		}
		if ('/' == substr($cache_base_dir,0,1)) {
			return $cache_base_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Cache_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$cache_base_dir.'/cache';
	}

	public function getLogDir()
	{
		$log_base_dir = $this->getAppSettings('log_base_dir');
		if (!$log_base_dir) {
			throw new Dase_Cache_Exception('no log_base_dir defined');
		}
		if ('/' == substr($log_base_dir,0,1)) {
			return $log_base_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Cache_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$log_base_dir.'/log';
	}

	public function getMediaDir()
	{
		$media_base_dir = $this->getAppSettings('media_base_dir');
		if (!$media_base_dir) {
			throw new Dase_Cache_Exception('no media_base_dir defined');
		}
		if ('/' == substr($media_base_dir,0,1)) {
			return $media_base_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Cache_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$media_base_dir.'/media';
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
		if ('/' != substr($conf_file,0,1)) {
			if (!$this->base_dir) {
				throw new Dase_Cache_Exception('no base_dir defined');
			}
			$conf_file = $this->base_dir.'/'.$conf_file;
		}
		if (file_exists($conf_file)) {
			$conf = $this->conf;
			include($conf_file);
			$this->conf = $conf;
		}
	}
}
