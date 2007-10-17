<?php

//adapted from http://talks.php.net/show/hpp/56

class Dase_FileCache {
	private $filename;
	private $expiration;
	private $cache_dir = CACHE_DIR;

	function __construct($file, $exp=10) {
		$this->filename = $file;
		$this->expiration = $exp;
	}

	function getLoc() {
		return "{$this->cache_dir}/{$this->filename}";
	}

	function get() {
		$filename = $this->getLoc();
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
		file_put_contents($this->getLoc(),$data);
	}
}

