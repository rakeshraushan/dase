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
	@include __autoloadFilename($class_name);
}

function __autoloadFilename($class_name) {
	return str_replace('_','/',$class_name) . '.php';
}

/**************************
 *
 *  environment
 *
 *************************/

$env['base_path'] = dirname(__FILE__);

/**************************
 *
 *  configuration object
 *
 *************************/

$c = new Dase_Config($env['base_path']);

//load main config
$c->load('inc/config.php');

//load local config
$c->load('inc/local_config.php');

/**************************
 *
 *  request object
 *
 *************************/

$r = new Dase_Http_Request($env);
$r->initPlugin($c->getCustomHandlers());

$cookie = new Dase_Cookie($r->app_root,$r->module,$c->getAuth('token'));

$cache = Dase_Cache::get($c->getCacheType(),$c->getCacheDir());

/************************
 *
 *  logging
 *
 **********************/

//just an experiment
if ($r->remote_addr) {
	$log = new Dase_Log($c->getLogDir().'/'.$r->remote_addr,Dase_Log::DEBUG);
} else {
	$log = new Dase_Log($c->getLogDir().'/dase.log',Dase_Log::DEBUG);
}

$db = new Dase_DB($c->get('db'),$log);

//request is going to be our object store (hmmm...)
$r->store('config',$c);
$r->store('cookie',$cookie);
$r->store('cache',$cache);
$r->store('db',$db);
$r->store('log',$log);

$app = new Dase($r);

//ab -n 300 -c 10 {app_root}
//print "ok"; exit;

$app->run();
