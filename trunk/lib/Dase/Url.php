<?php

class Dase_Url 
{
	private $path;
	private $root;
	private $params = array();

	public function __construct($config,$path,$is_upload=false) 
	{
		if ($is_upload) {
			//upload server  is set to accept larger files
			$this->root = $config->getAppSettings('remote_upload_url');
		} else {
			$this->root = $config->getAppSettings('remote_url');
		}
		$this->path = $path;
	}

	/* allows configured url to be overridden */
	public function setRoot($root)
	{
		$this->root = $root;
	}

	public function set($key,$val) 
	{
		$this->params[$key] = $val;
	}

	public function getUrl()
	{
		$url = trim($this->root,'/').'/';
		$url .= $this->path;
		$url .= '?';
		foreach ($this->params as $k => $v) {
			$url .= '&'.urlencode($k).'='.urlencode($v);
		}
		return $url;
	}
}

