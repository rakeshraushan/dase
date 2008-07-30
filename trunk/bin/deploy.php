#!/usr/bin/php
<?php

/*********** CONFIGURATION ********************/

$app = 'dase';
$target = '/opt/local/apache2/htdocs/'.$app;
$rewrite_base = $app; 
$httpd_group = 'www';

/**********************************************/

$working = dirname(__FILE__) . '/..';

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

apacheWrite("$working/cache", $httpd_group);
apacheWrite("$working/log/error.log", $httpd_group);
apacheWrite("$working/log/dase.log", $httpd_group);

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
