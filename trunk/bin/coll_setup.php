<?php


include 'config.php';

$app_root = 'http://myapproot';
$collection_ascii_id = 'test';
$http_user = '';
$http_pass = '';


$atts="
	one
	two
	three
	four
	five
	";

$types="
	one
	two
	three
	four
	five
	";


$entry = new Dase_Atom_Entry_Collection;
$entry->setTitle('Test Collection');
$entry->setId('test');
$entry->addLink($app_root.'/collection/'.$collection_ascii_id,'self');
foreach(explode("\n",$atts) as $att) {
	$att = trim($att);
	if ($att) {
		$entry->addAttribute($att);
	}
}
foreach(explode("\n",$types) as $type) {
	$type = trim($type);
	if ($type) {
		$entry->addItemType($type);
	}
}

if (!isset($argv[1]) || ($argv[1] != 'update' && $argv[1] != 'view')) {
	print "\nusage: php coll_update.php 'update|view'\n\n";exit;
}
if ('view' == $argv[1]) {
	print $entry->asXml();
}
if ('update' == $argv[1]) {
	print $entry->putToUrl($entry->getSelf(),$http_user,$http_pass);
}



