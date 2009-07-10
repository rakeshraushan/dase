<?php

include 'config.php';
include(DASE_CONFIG);
include(DASE_LOCAL_CONFIG);

print "enter serviceuser: ";
$serviceuser = trim(fgets(STDIN)); 
$tok = $conf['service_token']; 
if (!$tok) {
	print "no service token\n";
	exit;
}
if (!isset($conf['serviceuser'][$serviceuser])) {
	print "no such service user\n";
	exit;
}
print md5($conf['service_token'].$serviceuser)."\n"; 


