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

class Dase 
{
	public static function run()
	{
		$request = new Dase_Http_Request;
		//http://www.jcinacio.com/2007/04/19/phps-__tostring-magic-method-not-so-magic-before-520/
		Dase_Log::debug($request->__toString());
		if ($request->module) {
			//modules, by convention, have one handler in a file named
			//'handler.php' with classname {Module}ModuleHandler
			$handler_file = DASE_PATH.'/modules/'.$request->module.'/handler.php';
			$classname = ucfirst($request->module) . 'ModuleHandler';
		} else {
			include(DASE_PATH.'/handlers/'.$request->handler.'.php');
			$classname = ucfirst($request->handler).'Handler';
		}
		if (class_exists($classname,false)) {
			$handler = new $classname;
			$handler->dispatch($request);
		} else {
			Dase::error(404);
		}
	}

	public static function getConfig($key)
	{
		$conf = array();
		include(DASE_CONFIG);
		if (isset($conf[$key])) {
			return $conf[$key];
		} else {
			throw new Exception("no such configuration key: $key");
		}
	}
}
