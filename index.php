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

$c = new Dase_Config(dirname(__FILE__));

//load main config
$c->load('inc/config.php');

//load local config
$c->load('inc/local_config.php');

$dase_http_auth = new Dase_Http_Auth($c->getAuth());

$r = new Dase_Http_Request(dirname(__FILE__),$dase_http_auth);
$r->initPlugin($c->getCustomHandlers());

$cookie = new Dase_Cookie($r->app_root,$r->module,$c->getAuth('token'));

$cache = Dase_Cache::get($c->getCacheType(),$c->getCacheDir());

//just an experiment
if ($r->getRemoteAddr()) {
	$log = new Dase_Log($c->getLogDir().'/'.$r->getRemoteAddr(),Dase_Log::DEBUG);
} else {
	$log = new Dase_Log($c->getLogDir().'/dase.log',Dase_Log::DEBUG);
}

$db = new Dase_DB($c->get('db'),$log);

$dbuser = new Dase_DBO_DaseUser($db);
$dbuser->setAuth($c->getAuth());

$r->store('config',$c);
$r->store('cookie',$cookie);
$r->store('cache',$cache);
$r->store('db',$db);
$r->store('dbuser',$dbuser);
$r->store('log',$log);

$app = new Dase($r);

//ab -n 300 -c 10 {app_root}
//print "ok"; exit;

$app->run();
