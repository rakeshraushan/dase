<?php
include 'config.php';

$source = 'diia';
$target = 'cie';

$c = Dase_DBO_Collection::get($db,$source);

foreach ($c->getItems() as $item) {
	$json_doc = $item->retrieveJsonDoc('http://dase.laits.utexas.edu');
	$target_url = 'https://daseupload.laits.utexas.edu/collection/'.$target.'/ingester';
	print $target_url."\n";
	$res = Dase_Http::post($target_url,$json_doc,'pkeane','dupload','application/json');
	print_r($res);
}


