<?php

include 'cli_setup.php';

$t = array(
	'edfrf dfdfg sg f - rg-fd-fg ',
	'345345635673%^^&^^%4w 4t 5 55',
	'ab-ttt',
);

foreach ($t as $s) {
	print Dase_Util::dirify($s)."\n";
}

