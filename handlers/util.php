<?php

class UtilHandler
{
	public static function safeCheckHtml() {
		$text = html_entity_decode(file_get_contents('php://input'));
		$text = str_replace("&","&amp;",$text);
		$text = str_replace("'", "&apos;", $text);  
		$text = str_replace("\"", "&quot;", $text);
		include(DASE_PATH . '/lib/SafeHtmlChecker.class.php');
		$checker = new SafeHtmlChecker;
		$checker->check('<all>'.$text.'</all>');
		Dase::display($checker->getResultsAsJson(),false);
	}

	public static function test() {
		$db = Dase_DB::get();
		$sth = $db->query('select * from proposal');
		echo "hello";
		exit;
	}
}
