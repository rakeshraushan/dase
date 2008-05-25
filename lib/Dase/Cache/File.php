<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_Cache_File extends Dase_Cache 
{
	private $cache_dir = CACHE_DIR;
	private $contents;
	private $filename;
	private $tempfilename;
	private $ttl = CACHE_TTL;

	function __construct($http_request_or_filename)
	{
		if ('Dase_Http_Request' == get_class($http_request_or_filename)) {
			$r = $http_request_or_filename;
			$this->filename = md5($r->url.$r->response_mime_type);
		} else {
			$filename = $http_request_or_filename;
			if (strpos($filename,'?')) {
				//this prevents module writers from trouncing on a
				//cache (request cache always includes a '?')
				//beware: module writer could break app w/ cache named 'routes'
				throw new Exception('prohibited character in cache file name');
			}
			$this->filename = md5($filename);
		}
		$this->tempfilename = $this->cache_dir . $this->filename . '.' . getmypid() . $_SERVER['SERVER_ADDR'];
	}

	function expire()
	{
		//Dase::log('standard','expired ' . $this->getLoc());
		@unlink($this->getLoc());
	}

	function getLoc()
	{
		return $this->cache_dir . $this->filename;
	}

	function isFresh($ttl=null)
	{
		//clean up this logic
		$filename = $this->getLoc();
		if (!file_exists($filename)) {
			return false;
		}

		$time_to_live = $ttl ? $ttl : $this->ttl;

		$stat = @stat($filename);
		if($stat[9]) {
			if(time() > $stat[9] + $time_to_live) {
				@unlink($filename);
				return false;
			}
		}
		$this->contents = @file_get_contents($filename);
		return true;
	}

	function display()
	{
		echo $this->contents;
		exit;
	}

	function getData()
	{
		if ($this->isFresh()) {
			return $this->contents;
		}
	}

	function setData($data)
	{ 
		//avoids race condition
		if ($data) {
			file_put_contents($this->tempfilename,$data);
			rename($this->tempfilename, $this->getLoc());
			$this->contents = $data;
		}
	}
}

