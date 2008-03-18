<?php

class Dase_Log
{
	public static function put($logfile,$msg)
	{
		$date = date(DATE_W3C);
		$msg = "$date : $msg\n";
		if(file_exists(LOG_DIR . "{$logfile}.log")) {
			file_put_contents(LOG_DIR ."{$logfile}.log",$msg,FILE_APPEND);
		}
		if ('error' == $logfile) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_contents();
			ob_end_clean();
			file_put_contents(LOG_DIR ."error.log",$trace,FILE_APPEND);
		}
	}
}

