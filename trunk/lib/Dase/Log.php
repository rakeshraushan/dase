<?php

class Dase_Log 
{
	public static function logRemoteCall($msg) {
		file_put_contents(DASE_PATH .'/log/remote.log',$msg,FILE_APPEND);
	}

	public static function write($msg) {
		file_put_contents(DASE_PATH .'/log/standard.log',$msg,FILE_APPEND);
	}

	public static function error($msg) {
		file_put_contents(DASE_PATH .'/log/error.log',$msg,FILE_APPEND);
	}

	public static function sql($msg) {
		file_put_contents(DASE_PATH .'/log/sql.log',$msg,FILE_APPEND);
	}
}
