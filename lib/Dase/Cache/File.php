<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_Cache_File extends Dase_Cache 
{
	private $filename;
	private $tempfilename;
	private $ttl = 3;
	private $cache_dir = CACHE_DIR;

	function __construct($file='')
	{
		if ($file) {
			if (strpos($file,'?')) {
				//this prevents module writers from trouncing on a
				//cache (request cache always includes a '?')
				//beware: module writer could break app w/ cache named 'routes'
				throw new Exception('prohibited character in cache file name');
			}
			$this->filename = md5($file);
		} else {
			$this->filename = md5(Dase_Url::get());
		}
		$this->tempfilename = $this->cache_dir . $this->filename . '.' . getmypid() . $_SERVER['SERVER_ADDR'];
	}

	function setTimeToLive($exp)
	{
		$this->ttl = $exp;
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

	function getData()
	{
		//clean up this logic
		$filename = $this->getLoc();
		if (!file_exists($filename)) {
			return false;
		}
		if($this->ttl) {
			$stat = @stat($filename);
			if($stat[9]) {
				if(time() > $stat[9] + $this->ttl) {
					@unlink($filename);
					return false;
				}
			}
		}
		return @file_get_contents($filename);
	}

	function setData($data)
	{ 
		//avoids race condition
		if ($data) {
			file_put_contents($this->tempfilename,$data);
			rename($this->tempfilename, $this->getLoc());
		}
	}
}

