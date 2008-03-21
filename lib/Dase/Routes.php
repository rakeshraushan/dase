<?php

/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

//the purpose of the class is to convert the "easy-to-hand-edit"
//routes config files into "easy-to-parse" dispatch tree for DASe

class Dase_Routes
{
	public static $route_defaults = array(
		'method' => 'get',
		'mime' => 'text/html',
		'cache' => 'standard', //'standard','custom', or 'none'
	);

	public static function compile()
	{
		$cache = Dase_Cache::get('routes');
		$cached_routes = $cache->getData();
		if ($cached_routes) {
			if (defined("DEBUG")) {
				Dase_Log::put('standard','------- using cache -------');
				Dase_Log::put('standard','routes cache hit');
				Dase_Log::put('standard','---------------------------');
			}
			return unserialize($cached_routes);
		} else {
			$core_routes = Dase_Routes::compileRoutes(DASE_PATH.'/inc/routes.php');
			$all_routes = Dase_Routes::compileModuleRoutes(DASE_PATH.'/modules',$core_routes);
			$cache->setData(serialize($all_routes));
			return $all_routes;
		}
	}

	public static function compileRoutes($config_file)
	{
		//route defaults can be overridden in routes.php
		$route_defaults = self::$route_defaults;
		$compiled_routes = array();
		include $config_file;
		foreach (array('get','post','put','delete') as $method) {
			$compiled_routes[$method] = array();
			foreach ($routes as $handler => $methods) {
				foreach ($methods as $action => $params) {
					if (!is_array($params['uri_template'])) {
						$params['uri_template'] = array($params['uri_template']);
					}
					foreach ($params['uri_template'] as $uri_template) {
						$num = preg_match_all("/{([^{]*)}/",$uri_template,$matches);
						$regex = "^".$uri_template."$";
						if ($num) {
							$params['params'] = join('/',$matches[1]);
							$regex = preg_replace("/{[^{]*}/","([^/]*)",$regex);
						}
						foreach ($route_defaults as $key => $default_value) {
							if (!isset($params[$key])) {
								$params[$key] = $default_value;
							}
						}
						if ($params['method'] == $method) {
							$params['handler'] = $handler;
							$params['action'] = $action;
							foreach ($params as $k => $v) {
								if ('uri_template' == $k) { continue; }
								$compiled_routes[$method][$regex][$k] = $v;
							}
						}
					}
				}
			}
		}
		return $compiled_routes;
	}


	public static function getModuleRoutes($modules_dir,$module)
	{
		$routes = array();
		//allows us to avoid polluting global namespace w/ $routes
		$module_routes_file = $modules_dir.'/'.$module.'/routes.php';
		if (file_exists($module_routes_file)) {
			//note that module routes.php is being TRUSTED here 
			//(potential security hole if we are not careful)
			//modules can ONLY be added by app admin!!!
			include $module_routes_file;
		}
		return $routes;
	}

	public static function compileModuleRoutes($modules_dir,$compiled_routes)
	{
		//route defaults can be overridden in routes.php
		$route_defaults = self::$route_defaults;
		$dir = new DirectoryIterator($modules_dir);
		foreach ($dir as $module) {
			if (!$module->isDot()) {
				$routes = Dase_Routes::getModuleRoutes($modules_dir,$module);
				//a module can declare a collection that all operation 
				//apply to
				if (!isset($routes['collection'])) {
					$routes['collection'] = '';
				}
				foreach (array('get','post','put','delete') as $method) {
					foreach ($routes as $action => $params) {
						if ('collection' == $action) { continue; }
						if (!is_array($params['uri_template'])) {
							$params['uri_template'] = array($params['uri_template']);
						}
						foreach ($params['uri_template'] as $uri_template) {
							$num = preg_match_all("/{([^{]*)}/",$uri_template,$matches);
							//trim so that an empty uri_template does not have trailing slash
							$regex = trim("^modules/$module/".$uri_template,'/').'$';
							if ($num) {
								$params['params'] = join('/',$matches[1]);
								$regex = preg_replace("/{[^{]*}/","([^/]*)",$regex);
							}
							foreach ($route_defaults as $key => $default_value) {
								if (!isset($params[$key])) {
									$params[$key] = $default_value;
								}
							}
							if ($params['method'] == $method) {
								$params['action'] = $action;
								$params['name'] = $module->getFilename();
								$params['prefix'] = '/modules/'.$module;
								$params['collection_ascii_id'] = $routes['collection'];
								foreach ($params as $k => $v) {
									if ('uri_template' == $k) { continue; }
									$compiled_routes[$method][$regex][$k] = $v;
								}
							}
						}
					}
				}
			}
		}
		return $compiled_routes;
	}
}
