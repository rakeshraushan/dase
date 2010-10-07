<?php

function raw_input($msg,$supress_echo=false)
{
	print "$msg:\n";
	if ($supress_echo) {
		system('stty -echo');
		$input = trim(fgets(STDIN));
		system('stty echo');
	} else {
		$input = trim(fgets(STDIN));
	}
	return $input; 
}

//$msg = "configure handlers? [Y|n]?";


