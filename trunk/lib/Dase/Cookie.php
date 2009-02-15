<?php

class Dase_Cookie {

	//this class deals with the eid cookie and the 
	//encrypted eid cookie AND provides minimal
	//generic functionality

	protected $user_cookiename = 'DASE_USER';
	protected $auth_cookiename = 'DASE_AUTH';
	protected $cookiemap = array(
		'max' => 'DASE_MAX_ITEMS',
		'display' => 'DASE_DISPLAY_FORMAT',
		'module' => 'DASE_MODULE',
	);
	protected $display_cookiename = 'DASE_DISPLAY_FORMAT';
	protected $token;

	public function __construct($config)
	{
		$this->config = $config;
	}


	private function getPrefix() 
	{
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, but that's no good when browser & server
		//dates disagree)
		$app_root = $this->config->get('app_root');
		$prefix = str_replace('http://','',$app_root);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public function setEid($eid) 
	{
		$pre = Dase_Cookie::getPrefix();
		$key = md5($this->config->get('token').$eid);
		setcookie($pre . $this->user_cookiename,$eid,0,'/');
		setcookie($pre . $this->auth_cookiename,$key,0,'/');
	}

	public function set($type,$data) 
	{
		$pre = Dase_Cookie::getPrefix();
		if ('module' == $type) {
			$module = $this->config->get('module');
			$pre = $pre.$module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			$cookiename = $pre . $this->cookiemap[$type];
			setcookie($cookiename,$data,0,'/');
		}
	}

	public function get($type) 
	{
		$pre = Dase_Cookie::getPrefix();
		if ('module' == $type) {
			$module = $this->config->get('module');
			$pre = $pre.$module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			$cookiename = $pre . $this->cookiemap[$type];
			if (isset($_COOKIE[$cookiename])) {
				return $_COOKIE[$cookiename];
			}
		}
	}

	public function clearByType($type) 
	{
		$pre = Dase_Cookie::getPrefix();
		if ('module' == $type) {
			//allows each module their own module cookie
			$module = $this->config->get('module');
			$pre = $pre.$module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			setcookie($pre . $this->cookiemap[$type],"",-86400,'/');
		}
	}

	/** simply checks the cookie */
	public function getEid() 
	{
		$pre = Dase_Cookie::getPrefix();
		$token = $this->config->get('token');
		$key = '';
		$eid = '';
		if (isset($_COOKIE[$pre . $this->user_cookiename])) {
			$eid = $_COOKIE[$pre . $this->user_cookiename];
		}
		if (isset($_COOKIE[$pre . $this->auth_cookiename])) {
			$key = $_COOKIE[$pre . $this->auth_cookiename];
		}
		if ($key && $eid && $key == md5($token.$eid)) {
			return $eid;
		}
		return false;
	}

	public function clear() 
	{
		$pre = Dase_Cookie::getPrefix();
		setcookie($pre . $this->user_cookiename,"",-86400,'/');
		setcookie($pre . $this->auth_cookiename,"",-86400,'/');
	}
}


