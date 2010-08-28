<?php

class Dase_Url 
{

	public $root = 'https://dase.laits.utexas.edu';
	public $params = array();

	public function __construct($path,$root='') 
	{
		$this->path = $path;
		if ($root) {
			$this->root = $root;
		}
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

