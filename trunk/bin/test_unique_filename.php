<?php

include 'cli_setup.php';

$t = array(
	'jpg',
	'gif',
);

foreach ($t as $s) {
	print Dase_Util::getUniqueFilename($s)."\n";
}
	print Dase_Util::getUniqueFilename()."\n";

