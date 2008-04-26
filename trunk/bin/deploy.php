#!/usr/bin/php
<?php

//$target = '/mnt/projects/dase_modules/htdocs/';
//$target = '/var/www/html/dase_efossils/';
//$target = '/var/www/html/dase/';

/*********** CONFIGURATION ********************/

if (isset($argv[1])) {
	$app = $argv[1];
} else {
	$app = 'dase1';
}
$target = '/var/www/html/'.$app;
$rewrite_base = $app; 
$httpd_group = 'apache';

/**********************************************/

$working = dirname(__FILE__) . '/..';

$local_config = "$working/../{$app}_conf.php";

if (!file_exists($local_config)) {
	print "no local config file ($local_config)!\n";
	exit;
}

print "copying $working/* to $target\n";
print "...\n";

system("rsync -ar --delete --exclude='.svn' -e ssh $working/* $target");
print "copying $local_config to $target/inc/local_config.php\n";
system("rsync -ar  -e ssh $local_config $target/inc/local_config.php");

//create and write out .htaccess file
$htaccess =<<<EOD
DirectoryIndex index.php index.html
AddType application/x-httpd-php .php
Options FollowSymLinks
FileETag none

RewriteEngine On
RewriteBase  /$rewrite_base  

php_flag magic_quotes_gpc off

# media files go straight to media/server.php
RewriteRule ^media/([0-9a-z_]*)/([a-z3]*)/([^/]*)$ media/server.php?collection=$1&size=$2&filename=$3 [PT]

RewriteCond %{REQUEST_FILENAME} !-f 
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [PT] 
EOD;

$bytes = file_put_contents($target.'/.htaccess',$htaccess);
if ($bytes) {
	print "\n".$bytes . " bytes written to .htaccess\n";
} else {
	print "error writing .htaccess\n";
}

apacheWrite("$working/cache", $httpd_group);
apacheWrite("$working/log/error.log", $httpd_group);
apacheWrite("$working/log/sql.log", $httpd_group);
apacheWrite("$working/log/remote.log", $httpd_group);
apacheWrite("$working/log/standard.log", $httpd_group);
apacheWrite("$local_config", $httpd_group,0750);

print "done!\n";

function apacheWrite($file,$httpd_group,$mod=0775) {
	if (!file_exists($file)) {
		touch($file);
	}
	if (chgrp($file, $httpd_group) && chmod($file,$mod)) {
		print "set permissions on $file\n";
		return true;
	} else {
		print 'cannot set permissions';
		exit;
	}
}
