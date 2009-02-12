<?php

include 'config.php';

print "enter serviceuser: ";
$serviceuser = trim(fgets(STDIN)); 

print Dase_Auth::getServicePassword($serviceuser);
print "\n";

