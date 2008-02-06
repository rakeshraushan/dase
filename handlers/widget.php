<?php

class WidgetHandler
{
	public static function init() {

		//needs to be given a LONG ttl

		$jscode = '';
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/modules/'));
		foreach ($dir as $file) {
			if ('widget.js' == $file->getFilename()) {
				$jscode .= file_get_contents($file->getPathname());
			}
		}
		Dase::display($jscode);
	}
}

