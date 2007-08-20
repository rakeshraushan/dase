<?php

$sernum = $params['sernum'];
$days = Dase::filterGet('days');
$q = Dase::filterGet('q');

$url = APP_ROOT . "/modules/vrc/$sernum";
$sxe = new SimpleXMLElement($url, NULL, TRUE);

$file = $sxe->item[0]['digital_file'];
print $file;
foreach ($sxe->item[0]->metadata as $m) {
	print $m['attribute_ascii_id'] . "-> $m\n";
}
exit;
