<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_Cache_File extends Dase_Cache 
{
	private $cache_dir = CACHE_DIR;
	private $filename;
	private $tempfilename;
	private $ttl = 10; //10 seconds

	function __construct($filename)
	{
		if (!$filename) {
			throw new Exception('missing cache file name');
		}

		Dase_Log::debug('cache construct '.$filename);
		$this->filename = md5($filename);
		$this->tempfilename = $this->cache_dir . $this->filename . '.' . getmypid() . $_SERVER['SERVER_ADDR'];
	}

	public static function expunge() 
	{
		foreach (new DirectoryIterator(CACHE_DIR) as $file) {
			if (! $file->isDot()) {
				unlink($file->getPathname());
			}
		}
	}

	function expire()
	{
		$filename = $this->cache_dir . $this->filename;
		Dase_Log::debug('expired ' . $filename);
		@unlink($filename);
	}

	function getData($ttl=0)
	{
		$filename = $this->cache_dir . $this->filename;
		if (!file_exists($filename)) {
			Dase_Log::debug('cache cannot find '.$filename);
			return false;
		}

		$time_to_live = $ttl ? $ttl : $this->ttl;
		$stat = stat($filename);
		if(time() > $stat[9] + $time_to_live) {
			@unlink($filename);
			Dase_Log::debug('cache is stale '.$filename);
			return false;
		}
		Dase_Log::debug('cache HIT!!! '.$filename);
		return file_get_contents($filename);
	}

	function setData($data)
	{ 
		//avoids race condition
		if ($data) {
			file_put_contents($this->tempfilename,$data);
			rename($this->tempfilename,$this->cache_dir.$this->filename);
		}
		return $data;
	}
}

