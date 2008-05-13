<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_Cache_File extends Dase_Cache 
{
	private $filename;
	private $tempfilename;
	private $ttl = CACHE_TTL;
	private $cache_dir = CACHE_DIR;

	function __construct($filename='')
	{
		if ($filename) {
			if (strpos($filename,'?')) {
				//this prevents module writers from trouncing on a
				//cache (request cache always includes a '?')
				//beware: module writer could break app w/ cache named 'routes'
				throw new Exception('prohibited character in cache file name');
			}
			$this->filename = md5($filename);
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

	function getData($send_headers=true)
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
		if ($send_headers) {
			$this->sendHeaders();
		}
		return @file_get_contents($filename);
	}

	function sendHeaders() 
	{
		$meta_filename = $this->getLoc().'.meta';
		if (!file_exists($meta_filename)) {
			return false;
		}
		$headers = unserialize(@file_get_contents($meta_filename)); 
		if (is_array($headers)) {
			foreach ($headers as $header) {
				header($header);
			}
		}
	}

	function setData($data,$headers=null)
	{ 
		//avoids race condition
		if ($data) {
			file_put_contents($this->tempfilename,$data);
			rename($this->tempfilename, $this->getLoc());

			//and headers
			if ($headers && is_array($headers)) {
				file_put_contents($this->tempfilename.'.meta',serialize($headers));
				rename($this->tempfilename.'.meta', $this->getLoc().'.meta');
			}
		}
	}
}

