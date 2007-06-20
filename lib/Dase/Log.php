<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

class Dase_Log 
{
	public static function logRemoteCall($msg) {
		$writer = new Zend_Log_Writer_Stream(DASE_PATH .'/log/remote.log');
		$logger = new Zend_Log($writer);
		$logger->info($msg);
	}

	public static function write($msg) {
		$writer = new Zend_Log_Writer_Stream(DASE_PATH .'/log/standard.log');
		$logger = new Zend_Log($writer);
		$logger->info($msg);
	}

	public static function error($msg) {
		$writer = new Zend_Log_Writer_Stream(DASE_PATH .'/log/error.log');
		$logger = new Zend_Log($writer);
		$logger->err($msg);
	}

	public static function sql($msg) {
		$writer = new Zend_Log_Writer_Stream(DASE_PATH .'/log/sql.log');
		$logger = new Zend_Log($writer);
		$logger->info($msg);
	}
}
