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

$config = new Dase_Config();

//load main config
$config->load(dirname(__FILE__).'/inc/config.php');

//load local config
$config->load(dirname(__FILE__).'/inc/local_config.php');

$dase_http_auth = new Dase_Http_Auth($config->getAuth());

$r = new Dase_Http_Request(dirname(__FILE__),$dase_http_auth);
$r->initPlugin($config->getCustomHandlers());

$cookie = new Dase_Cookie($r->app_root,$r->module,$config->getAuth('token'));
$cache = Dase_Cache::get(
	$config->getAppSettings('cache_type'),
	$config->getAppSettings('cache_dir'),
	$r->getServerIp()
);
$db = new Dase_DB($config->get('db'));

//will be used by Dase_Http_Request 
$dbuser = new Dase_DBO_DaseUser($db);
$dbuser->setAuth($config->getAuth());

$r->store('config',$config);
$r->store('cookie',$cookie);
$r->store('cache',$cache);
$r->store('db',$db);
$r->store('dbuser',$dbuser);

$app = new Dase($r);

//ab -n 300 -c 10 {app_root}
//print "ok"; exit;

$app->run();
