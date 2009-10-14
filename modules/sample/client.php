<?php

include 'DaseClient.php';

$client = new DaseClient('keanepj');
$res = $client->search('e*');

$app_root = $res->app_root;
$total = $res->total;

$html ="<html><head><title>DaseClient Sample</title></head><body>";
$html .="<h1>DaseClient Sample</h1>";
$html .="<h3>$total items found</h3>";
$html .="<ul>";

foreach ($res->items as $item) {
	if (isset($item->metadata->title)) {
		$html .='<li><img src="'.$app_root.'/'.$item->media->thumbnail.'">'.$item->metadata->title[0]."</li>\n";
	}
}

$html .="</ul>";
$html .="</body></html>";

echo $html;
