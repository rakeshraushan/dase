<?php

class Dase_Cache_File extends Dase_Cache 
{
	private $cache_dir;
	private $filename;
	private $pid; //process id
	private $server_ip;
	private $tempfilename;
	private $ttl;

	function __construct($cache_dir,$server_ip='localhost',$ttl=10)
	{
		$this->cache_dir = $cache_dir.'/files'; //always append subdir as expunge precaution
		$this->ttl = $ttl;
		$this->server_ip = $server_ip;
		$this->pid = getmypid();
		$this->_initDir();
	}

	private function _initDir()
	{
		if ('/files' == $this->cache_dir) {
			throw new Dase_Cache_Exception("no cache directory specified");
		}
		if (!file_exists($this->cache_dir)) {
			if (!mkdir($this->cache_dir,0770,true)) {
				throw new Dase_Cache_Exception("cannot create directory: ".$this->cache_dir);
			}
		}
	}

	public function expungeByHash($md5_hash)
	{
		if ($md5_hash) {
			$filename = $this->cache_dir . $md5_hash;
			Dase_Log::get()->debug('expired ' . $filename);
			@unlink($filename);
		}
	}

	public function expunge() 
	{
		$i = 0;
		//from PHP Cookbook 2nd. ed p. 718
		$iter = new RecursiveDirectoryIterator($this->cache_dir);
		foreach (new RecursiveIteratorIterator($iter,RecursiveIteratorIterator::CHILD_FIRST) as $file) {
			if (false === strpos($file->getPathname(),'.svn')) {
				if ($file->isDir()) {
					$i++;
					rmdir($file->getPathname());
				} else {
					$i++;
					unlink($file->getPathname());
				}
			}
		}
		return $i;
	}

	public function setCacheDir($cache_dir)
	{
		$this->cache_dir = $cache_dir.'/files';
		$this->_initDir();
	}

	public function getCacheDir()
	{
		return $this->cache_dir;
	}

	public function expire($filename)
	{
		$filename = $this->cache_dir . $filename;
		Dase_Log::get()->debug('expired ' . $filename);
		@unlink($filename);
	}

	/** any data fetch can override the default ttl */
	public function getData($filename,$ttl=0)
	{
		$filename = $this->cache_dir . $filename;
		if (!file_exists($filename)) {
			Dase_Log::get()->debug('cache cannot find '.$filename);
			return false;
		}

		$time_to_live = $ttl ? $ttl : $this->ttl;
		$stat = stat($filename);
		if(time() > $stat[9] + $time_to_live) {
			@unlink($filename);
			Dase_Log::get()->debug('cache is stale '.$filename);
			return false;
		}
		Dase_Log::get()->debug('cache HIT!!! '.$filename);
		return file_get_contents($filename);
	}

	public function setData($filename,$data)
	{ 
		$tempfilename = $this->cache_dir.$filename.$this->pid.$this->server_ip;
		//avoids race condition
		if ($data) {
			file_put_contents($tempfilename,$data);
			rename($tempfilename,$this->cache_dir.$filename);
		}
		return $this->filename;
	}
}

