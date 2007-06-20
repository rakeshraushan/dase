<?php

class Dase_Encryption {

	//from Advanced PHP p.352
	//mcrypt info
	static $cypher = 'blowfish';
	static $mode = 'cfb';
	static $key = 'zaqwsxmjuik';

	public static function encrypt($plaintext) {
		$td = mcrypt_module_open(self::$cypher,'',self::$mode,'');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td,self::$key,$iv);
		$crypttext = mcrypt_generic($td,$plaintext);
		mcrypt_generic_deinit($td);
		return $iv.$crypttext;
	}

	public static function decrypt($crypttext) {
		$td = mcrypt_module_open(self::$cypher,'',self::$mode,'');
		$ivsize = mcrypt_enc_get_iv_size($td);
		$iv = substr($crypttext,0,$ivsize);
		$crypttext = substr($crypttext,$ivsize);
		mcrypt_generic_init($td,self::$key,$iv);
		$plaintext = mdecrypt_generic($td,$crypttext);
		mcrypt_generic_deinit($td);
		return $plaintext;
	}
}


