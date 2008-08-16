<?php

class Dase_Log 
{
	private static $logfile = DASE_LOG;
	private static $log_level = LOG_LEVEL;

	private static function write($msg,$backtrace)
	{
		$date = date(DATE_W3C);
		$msg = $date.'| pid:'.getmypid().':'.$msg."\n";
		if(file_exists(self::$logfile)) {
			file_put_contents(self::$logfile,$msg,FILE_APPEND);
		}
		if ($backtrace) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_contents();
			ob_end_clean();
			file_put_contents(self::$logfile,$trace,FILE_APPEND);
		}
	}

	/** make sure the class is loaded,
		since it is not properly loaded when called from a destructor
	 */
	public static function start()
	{
	}

	public static function all($msg,$backtrace = false)
	{
		//very verbose
		if (self::$log_level >= 3) {
			self::write($msg,$backtrace);
		}
	}

	public static function debug($msg,$backtrace = false)
	{
		//notices helpful for debugging (including all sql)
		if (self::$log_level >= 2) {
			self::write($msg,$backtrace);
		}
	}

	public static function info($msg,$backtrace = false)
	{
		//normal notices, ok for production
		if (self::$log_level >= 1) {
			self::write($msg,$backtrace);
		}
	}
}
