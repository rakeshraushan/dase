<?php

define('LOG_LEVEL',3);
$database = 'dase_prod';
include 'cli_setup.php';

$c = Dase_DBO_Collection::get('medieval');

foreach($c->getItems() as $item) {
	foreach ($item->getValues() as $value) {
		$str = $value->value_text;
		if ($str != strip_invalid_xml_chars2($str)) {
			$value->value_text = strip_invalid_xml_chars2($str);
			$value->update();
			print "updated item $item->serial_number\n";
		}
	}
}


function strip_invalid_xml_chars2( $in ) {
	$out = "";
	$length = strlen($in);
	for ( $i = 0; $i < $length; $i++) {
		$current = ord($in{$i});
		if ( ($current == 0x9) || ($current == 0xA) || ($current == 0xD) || (($current >= 0x20) && ($current <= 0xD7FF)) || (($current >= 0xE000) && ($current <= 0xFFFD)) || (($current >= 0x10000) && ($current <= 0x10FFFF))){
			$out .= chr($current);
		} else{
			$out .= " ";
		}
	}
	return $out;
}
