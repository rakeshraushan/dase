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


ini_set('include_path','lib');

//PHP ERROR REPORTING -- turn off for production
ini_set('display_errors',1);
error_reporting(E_ALL);


function __autoload($class_name) {
	include_once __autoloadFilename($class_name);
}

function __autoloadFilename($class_name) {
	return str_replace('_','/',$class_name) . '.php';
}

$logfile = dirname(__FILE__).'/log/dase.log';
Dase_Log::get()->start($logfile,Dase_Log::DEBUG);

$config = new Dase_Config($base_path);

//load main config
$config->load(dirname(__FILE__).'/inc/config.php');

//load local config
$config->load(dirname(__FILE__).'/inc/local_config.php');

$r = new Dase_Http_Request($config);
$r->set('cookie',new Dase_Cookie($config));

$app = new Dase($r);
$app->run();
