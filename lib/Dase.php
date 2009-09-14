<?php

class Dase_Exception extends Exception {}

class Dase
{
	public static function run($config)
	{
		try {
			$db = new Dase_DB($config);
		} catch (PDOException $e) {
			echo 'No database connection.  Has DASe been installed?';
			return;
		}

		if (file_exists(BASE_PATH.'/inc/local_bootstrap.php')) {
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

