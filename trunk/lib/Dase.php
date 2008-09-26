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
		Dase_Log::start($request);
		//http://www.jcinacio.com/2007/04/19/phps-__tostring-magic-method-not-so-magic-before-520/
		Dase_Log::debug("\n-----------------\n".$request->__toString()."-----------------\n");
		$classname = '';
		if ($request->module) {
			//modules, by convention, have one handler in a file named
			//'handler.php' with classname {Module}ModuleHandler
			$handler_file = DASE_PATH.'/modules/'.$request->module.'/handler.php';
			if (file_exists($handler_file)) {
				include "$handler_file";
				//will allow config request to include module config
				define('MODULE_PATH',DASE_PATH.'/modules/'.$request->module);
				Dase_Config::reload();
				//modules can carry their own libraries
				$new_include_path = ini_get('include_path').':'.MODULE_PATH.'/lib'; 
				ini_set('include_path',$new_include_path); 
				Dase_Log::debug('set include path to: '.$new_include_path);
				$classname = 'Dase_ModuleHandler_'.ucfirst($request->module);
			} else {
				$request->renderError(404,"no such handler: $handler_file");
			}
		} else {
			$classname = 'Dase_Handler_'.ucfirst($request->handler);
		}
		if (class_exists($classname,true)) {
			$handler = new $classname;
			$handler->dispatch($request);
		} else {
			$request->renderError(404,'no such handler class');
		}
	}
}
