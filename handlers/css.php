<?php

class CssHandler
{
	public static function init($request)
	{
		$cache = Dase_Cache::get('dynamic_css');
		if (!$cache->isFresh(333)) {
			$csscode = '';
			$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/modules/'));
			foreach ($dir as $file) {
				if ('export.css' == $file->getFilename()) {
					$csscode .= file_get_contents($file->getPathname());
				}
			}
			$headers = array("Content-Type: text/css; charset=utf-8");
			$cache->setData($css_code,$headers);
		}
		$cache->display();
	}
}

