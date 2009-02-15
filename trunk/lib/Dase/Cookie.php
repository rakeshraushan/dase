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
	protected $app_root;
	protected $module;
	protected $token;

	public function __construct($app_root,$module='',$token)
	{
		$this->app_root = $app_root;
		$this->module = $module;
		$this->token = $token;
	}

	private function getPrefix() 
	{
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, but that's no good when browser & server
		//dates disagree)
		$prefix = str_replace('http://','',$this->app_root);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public function setEid($eid) 
	{
		$pre = Dase_Cookie::getPrefix();
		$key = md5($this->token.$eid);
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
		$pre = $this->getPrefix();
		if ('module' == $type) {
			$pre = $pre.$this->module.'_';
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
		$pre = $this->getPrefix();
		if ('module' == $type) {
			//allows each module their own module cookie
			$pre = $pre.$this->module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			setcookie($pre . $this->cookiemap[$type],"",-86400,'/');
		}
	}

	/** simply checks the cookie */
	public function getEid() 
	{
		$pre = $this->getPrefix();
		$key = '';
		$eid = '';
		if (isset($_COOKIE[$pre . $this->user_cookiename])) {
			$eid = $_COOKIE[$pre . $this->user_cookiename];
		}
		if (isset($_COOKIE[$pre . $this->auth_cookiename])) {
			$key = $_COOKIE[$pre . $this->auth_cookiename];
		}
		if ($key && $eid && $key == md5($this->token.$eid)) {
			return $eid;
		}
		return false;
	}

	public function clear() 
	{
		$pre = $this->getPrefix();
		setcookie($pre . $this->user_cookiename,"",-86400,'/');
		setcookie($pre . $this->auth_cookiename,"",-86400,'/');
	}
}


