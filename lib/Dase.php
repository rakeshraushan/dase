<?php

class Dase_Exception extends Exception {}

class Dase
{
	public static function run($config)
	{
		$db = new Dase_DB($config);

		if (
			file_exists(BASE_PATH.'/inc/local_bootstrap.php') &&
			//make sure db is set up
			file_exists(BASE_PATH.'/inc/local_config.php')
		) {
			//allows db & config enabled bootstrap
			include BASE_PATH.'/inc/local_bootstrap.php';
		}

		$r = new Dase_Http_Request($config->getAppSettings('default_handler'));
		$r->initUser($db,$config);
		$r->initCache(Dase_Cache::get(CACHE_TYPE));
		$r->initCookie($config->getAuth('token'));
		$r->initAuth($config->getAuth());
		$r->initPlugin($config->getCustomHandlers());
		$r->logRequest();

		$handler = $r->getHandlerObject($db,$config);
		$handler->dispatch($r);
	}
}

