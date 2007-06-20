<?php

Class Dase_Plugins 
{

	private static $plugins = array();

	private function __construct() {}

	static public function act($collection_ascii_id = 'dase',$hook = 'load') { //use 'dase' for system-wide
		//I need to set these in a static element (i.e. cache 'em)
		$hook_array = explode('_',$hook);
		$hook_action = array_shift($hook_array);
		foreach ($hook_array as $hook_part) {
			$hook_action .= ucfirst($hook_part);
		}
		$coll = '';
		foreach (explode('_',$collection_ascii_id) as $part) {
			$coll .= ucfirst($part);
		}
		$dir = (DASE_PATH . "/plugins/$coll");
		if (is_dir($dir)) {
			foreach (new DirectoryIterator($dir) as $pfile) {
				if ($pfile->isDir() && !$pfile->isDot()) {
					$plugin = $pfile->getFilename();
					if (is_file("$dir/$plugin/Plugin.php")) {
						$class = $coll . '_' . $plugin . '_Plugin';
						if (method_exists($class,$hook_action)) {
							call_user_func(array($class,$hook_action));
						}
					}
				}
			}
		}
	}

	static public function filter($collection_ascii_id,$hook,$string) { 
		//I need to set these in a static element (i.e. cache 'em)
		$hook_array = explode('_',$hook);
		$hook_action = array_shift($hook_array);
		foreach ($hook_array as $hook_part) {
			$hook_action .= ucfirst($hook_part);
		}
		$hook_action .= 'Filter';
		$coll = '';
		foreach (explode('_',$collection_ascii_id) as $part) {
			$coll .= ucfirst($part);
		}
		$dir = (DASE_PATH . "/plugins/$coll");
		if (is_dir($dir)) {
			foreach (new DirectoryIterator($dir) as $pfile) {
				if ($pfile->isDir() && !$pfile->isDot()) {
					$plugin = $pfile->getFilename();
					if (is_file("$dir/$plugin/Plugin.php")) {
						$class = $coll . '_' . $plugin . '_Plugin';
						if (method_exists($class,$hook_action)) {
							$string = call_user_func_array(array($class,$hook_action),array($string));
						}
					}
				}
			}
		}
		return $string;
	}

	static public function load() {
		Dase_Plugins::act();
	}

	static public function get($collection_ascii_id) {
		$coll = '';
		foreach (explode('_',$collection_ascii_id) as $part) {
			$coll .= ucfirst($part);
		}
		return self::$plugins[$coll];
	}

	static public function getAll() {
		return self::$plugins;
	}
}

