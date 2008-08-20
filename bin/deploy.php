#!/usr/bin/php
<?php

$working = dirname(__FILE__) . '/..';
define('DASE_PATH',$working);

/*********** CONFIGURATION ********************/

//deployment settings
$target = '/var/www/html/dase1';
$httpd_group = 'apache';
$rewrite_base = 'dase1';

//$target = '/mnt/projects/efossils/htdocs/tb_dase';
//$rewrite_base = 'efossils/tb_dase';

/**********************************************/

print "copying $working/* to $target\n";
print "...\n";

system("rsync -ar --delete --exclude='.svn' -e ssh $working/* $target");

//create and write out .htaccess file
$htaccess =<<<EOD
DirectoryIndex index.php index.html
AddType application/x-httpd-php .php
Options FollowSymLinks
FileETag none

RewriteEngine On
RewriteBase  /$rewrite_base  

php_flag magic_quotes_gpc off

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

apacheWrite("$target/cache", $httpd_group);
apacheWrite("$target/log/error.log", $httpd_group);
apacheWrite("$target/log/dase.log", $httpd_group);

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
