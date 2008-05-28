<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_Cache_File extends Dase_Cache 
{
	private $cache_dir = CACHE_DIR;
	private $contents;
	private $filename;
	private $tempfilename;
	private $ttl = CACHE_TTL;

	function __construct($filename)
	{
		if (!$filename) {
			throw new Exception('missing cache file name');
		}

		Dase_Log::all('cache construct '.$filename);
		$this->filename = md5($filename);
		$this->tempfilename = $this->cache_dir . $this->filename . '.' . getmypid() . $_SERVER['SERVER_ADDR'];
	}

	function expire()
	{
		Dase_Log::debug('expired ' . $this->getLoc());
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
			Dase_Log::debug('cache cannot find '.$filename);
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

	function getData($ttl=null)
	{
		if ($this->isFresh($ttl)) {
			Dase_Log::debug('cache hit '.$this->getLoc().' ttl ='.$ttl);
			return $this->contents;
		} else {
			Dase_Log::debug('cache miss '.$this->getLoc().' ttl='.$ttl);
			return false;
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

