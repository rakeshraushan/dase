<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_FileCache {
	private $filename;
	private $tempfilename;
	private $expiration = 10;
	private $cache_dir = CACHE_DIR;

	function __construct($file='') {
		if ($file) {
			if (strpos($file,'?')) {
				//this prevents module writers from trouncing on a
				//cache (request cache always includes a '?')
				//beware: module writer could break app w/ cache named 'routes'
				throw new Exception('prohibited character in cache file name');
			}
			$this->filename = md5($file);
		} else {
			$this->filename = md5(Dase::instance()->request_url . '?' . Dase::instance()->query_string);
		}
		$this->tempfilename = $this->cache_dir . $this->filename . '.' . getmypid() . $_SERVER['SERVER_ADDR'];
	}

	function setExpiration($exp) {
		$this->expiration = $exp;
	}

	function getLoc() {
		return $this->cache_dir . $this->filename;
	}

	function get() {
		//clean up this logic
		$filename = $this->getLoc();
		if (!file_exists($filename)) {
			return false;
		}
		if($this->expiration) {
			$stat = @stat($filename);
			if($stat[9]) {
				if(time() > $stat[9] + $this->expiration) {
					unlink($filename);
					return false;
				}
			}
		}
		return @file_get_contents($filename);
	}

	function set($data) { 
		//avoids race condition
		if ($data) {
			file_put_contents($this->tempfilename,$data);
			rename($this->tempfilename, $this->getLoc());
		}
	}
}

