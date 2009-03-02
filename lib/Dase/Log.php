<?php

class Dase_Log_Exception extends Exception {}

class Dase_Log 
{
	private $filehandle;
	private $logfile;
	private $log_level;

	const OFF 		= 1;	// Nothing at all.
	const INFO 		= 2;	// Production 
	const DEBUG 	= 3;	// Most Verbose

	/** supply defaults so it is easy to create a fake log */
	public function __construct($log_dir,$logfile='dase.log',$log_level)
	{
		$this->logfile = $log_dir.'/'.$logfile;
		$this->log_level = $log_level;
	}

	public function __destruct()
	{
		if ($this->filehandle) {
			fclose($this->filehandle);
		}
	}

	public function is_writeable()
	{
		return is_writeable($this->logfile);
	}

	private function _init()
	{
		if (!$this->logfile || !is_writeable($this->logfile) || !$filehandle = fopen($this->logfile, 'a')) {
			return false;
		}
		$this->filehandle = $filehandle;
	}

	private function _write($msg,$backtrace=false)
	{
		$this->_init();
		$date = date(DATE_W3C);
		$msg = $date.' | pid: '.getmypid().' : '.$msg."\n";
		if ($backtrace) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$msg .= "\n".ob_get_contents();
			ob_end_clean();
		}
		if (fwrite($this->filehandle, $msg) === FALSE) {
			throw new Dase_Log_Exception('cannot write to logfile '.$this->logfile);
		}
		return true;
	}

	public function debug($msg,$backtrace = false)
	{
		//notices helpful for debugging (including all sql)
		if ($this->log_level >= Dase_Log::DEBUG) {
			$this->_write($msg,$backtrace);
		}
	}

	public function info($msg,$backtrace = false)
	{
		//normal notices, ok for production
		if ($this->log_level >= Dase_Log::INFO) {
			$this->_write($msg,$backtrace);
		}
	}

	public function truncate()
	{
		@unlink($this->logfile);
		return $this->_write("---- dase log ----\n\n");
	}

	public function getAsArray()
	{
		if ($this->logfile) {
			return file($this->logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		}
	}
}
