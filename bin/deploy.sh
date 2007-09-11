#!/usr/bin/php
<?php

$working = dirname(__FILE__) . '/..';
$target = '/var/www/html/dase/';
$httpd_group = 'apache';

print "copying $working/* to $target";
print "...";

system("rsync -ar --delete --exclude='.svn' -e ssh $working/* $target");
system("rsync -ar  -e ssh $working/prod_htaccess $target/.htaccess");
system("rsync -ar  -e ssh $working/../dase_build_conf.php $target/inc/local_config.php");

apacheWrite("$working/templates_c", 'apache');
apacheWrite("$working/log/error.log", 'apache');
apacheWrite("$working/log/sql.log", 'apache');
apacheWrite("$working/log/remote.log", 'apache');
apacheWrite("$working/log/standard.log", 'apache');

print "done!\n";

function apacheWrite($file,$httpd_group) {
if (chgrp($file, $httpd_group) && chmod($file, 0775)) {
	return true;
} else {
print 'cannot set permissions';
exit;
}
}
