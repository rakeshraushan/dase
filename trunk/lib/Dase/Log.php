<?php

class Dase_Log_Exception extends Exception {}

class Dase_Log 
{
	private $filehandle;
	private $logfile;
	private $log_level;
	private static $instance;

	const OFF 		= 1;	// Nothing at all.
	const INFO 		= 2;	// Production 
	const DEBUG 	= 3;	// Most Verbose

	final private function __construct() {}

	public function start($logfile,$log_level)
	{
		$this->log_level = $log_level;
		$this->logfile = $log_level;
		if (is_writable($logfile)) {
			if (!$filehandle = fopen($logfile, 'a')) {
				throw new Dase_Log_Exception('cannot open logfile '.$logfile);
			}
		} else {
			throw new Dase_Log_Exception('cannot write to logfile '.$logfile);
		}
		$this->filehandle = $filehandle;
	}

	public function stop()
	{
		if ($this->filehandle) {
			fclose($this->filehandle);
		}
	}

	public static function get()
	{
		if (is_null(self::$instance)) {
			self::$instance = new Dase_Log;
		}
		return self::$instance;
	}

	private function _write($msg,$backtrace)
	{
		$date = date(DATE_W3C);
		$msg = $date.'|pid:'.getmypid().':'.$msg."\n";
		if ($backtrace) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$msg .= "\n".ob_get_contents();
			ob_end_clean();
		}
		if (fwrite($this->filehandle, $msg) === FALSE) {
			throw new Dase_Log_Exception('cannot write to logfile '.$logfile);
		}
	}

	public function debug($msg,$backtrace = false)
	{
		//notices helpful for debugging (including all sql)
		if ($this->log_level >= 2) {
			$this->_write($msg,$backtrace);
		}
	}

	public function info($msg,$backtrace = false)
	{
		//normal notices, ok for production
		if ($this->log_level >= 1) {
			$this->_write($msg,$backtrace);
		}
	}

	public function truncate()
	{
		$this->stop();
		$this->start();
		return $this->_write("---- dase log ----\n\n");
	}
}
