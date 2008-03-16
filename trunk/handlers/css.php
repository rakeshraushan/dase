<?php

class CssHandler
{
	public static function init()
	{

		//needs to be given a LONG ttl

		$csscode = '';
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/modules/'));
		foreach ($dir as $file) {
			if ('export.css' == $file->getFilename()) {
				$csscode .= file_get_contents($file->getPathname());
			}
		}
		Dase::display($csscode);
	}
}

