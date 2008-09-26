<?php

class Dase_Log 
{
	private static $logfile;
	private static $log_level;
	private static $request;
	private static $started = 0;

	public static function start($request)
	{
		if (!self::$started) {
			self::$request = $request; 
			self::$logfile = DASE_LOG;
			self::$log_level = LOG_LEVEL;
		}
	}

	/** restart allows us to get request info 
	 * (like http users) later in the cycle
	 */
	public static function restart($request)
	{
		self::$request = $request; 
	}

	private static function write($msg,$backtrace)
	{
		$user = self::$request->getUser('any');
		if ($user) {
			$eid = $user->eid;
		} else {
			$eid = '';
		}
		$date = date(DATE_W3C);
		$msg = $date.'|user:'.$eid.'|pid:'.getmypid().':'.$msg."\n";
		if(file_exists(self::$logfile)) {
			@file_put_contents(self::$logfile,$msg,FILE_APPEND);
		}
		if ($backtrace) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_contents();
			ob_end_clean();
			@file_put_contents(self::$logfile,$trace,FILE_APPEND);
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
