<?php

class AdminHandler
{

	public static function getAcl($request)
	{
		$acl = Dase_Admin::getAcl();
		if (Dase_Filter::filterGet('as_php')) {
			Dase::display(var_export($acl,1),'text/plain');
		} else {
			Dase::display(Dase_Json::get($acl),'application/json');
		}
	}

	public static function getMediaSourceList($request)
	{
		$media_sources = Dase_DBO_Collection::getMediaSources();
		if (Dase_Filter::filterGet('as_php')) {
			Dase::display(var_export($media_sources,1),'text/plain');
		} else {
			Dase::display(Dase_Json::get($media_sources),'application/json');
		}
	}

	public static function phpinfo($request)
	{
		phpinfo();
		exit;
	}

	public static function exec($request) {
		if (chmod('/mnt/www-data/dase/media/early_american_history_collection/400',0775)) {
			echo "done!";
		} else {
			echo "did not work";
		}
	}

	public static function testMimeParser($request) {
		$supported = array(
			'text/html',
			'application/xhtml+xml',
			'application/atom+xml',
			'application/json',
			'application/atomsvc+xml',
			'application/xml'
		);

		$header = $_SERVER['HTTP_ACCEPT'];
		$mp = new Mimeparse;
		print $mp->best_match($supported,$header);
	}
}

