<?php


if (!isset($argv[1])) {
		print "\n";
		print "ERROR: missing colleciton_ascii_id\n";
		print "\n";
		print "syntax: get_collection_dump.php <collection_ascii_id>\n";
		print "\n";
		exit;
}

$ascii_id = $argv[1];

$url = 'https://dase.laits.utexas.edu/collection/'.$ascii_id.'/dump.json';
//$url = 'https://www.laits.utexas.edu/geodia/collection/'.$ascii_id.'/dump.json';

$json = file_get_contents($url);
file_put_contents('dump.json',$json);
