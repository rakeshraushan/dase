<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

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
