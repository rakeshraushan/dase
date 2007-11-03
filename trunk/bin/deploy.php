#!/usr/bin/php
<?php

$working = dirname(__FILE__) . '/..';
$target = '/var/www/html/dasetest/';
//$target = '/var/www/html/dase/';
$httpd_group = 'apache';

print "copying $working/* to $target";
print "...";

system("rsync -ar --delete --exclude='.svn' -e ssh $working/* $target");
system("rsync -ar  -e ssh $working/prod_htaccess $target/.htaccess");
system("rsync -ar  -e ssh $working/../dase_build_conf.php $target/inc/local_config.php");

apacheWrite("$working/templates_c", $httpd_group);
apacheWrite("$working/cache", $httpd_group);
apacheWrite("$working/log/error.log", $httpd_group);
apacheWrite("$working/log/sql.log", $httpd_group);
apacheWrite("$working/log/remote.log", $httpd_group);
apacheWrite("$working/log/standard.log", $httpd_group);

print "done!\n";

function apacheWrite($file,$httpd_group) {
	if (!file_exists($file)) {
		touch($file);
	}
	if (chgrp($file, $httpd_group) && chmod($file, 0775)) {
		return true;
	} else {
		print 'cannot set permissions';
		exit;
	}
}
