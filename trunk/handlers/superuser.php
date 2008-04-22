<?php

class SuperuserHandler
{

	public static function monitor($params) {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'experimental/index.xsl';
		Dase::display($t->transform());
	}

	public static function calendar($params) {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'experimental/calendar.xsl';
		$t->source = XSLT_PATH.'experimental/calendar.xml';
		Dase::display($t->transform());
	}

	public static function phpinfo($params)
	{
		phpinfo();
		exit;
	}

	public static function exec($params) {
		if (chmod('/mnt/www-data/dase/media/early_american_history_collection/400',0775)) {
			echo "done!";
		} else {
			echo "did not work";
		}
	}
}

