<?php

$database = "dase_prod";
include 'cli_setup.php';
$tally = array();
$c = new Dase_DB_Collection();
foreach($c->getAll() as $coll) {
	$tally[$coll['ascii_id']] = 0;
	$a = new Dase_DB_Attribute;
	$a->collection_id = $coll['id'];
	foreach ($a->findAll() as $arow) {
		$value = new Dase_DB_Value;
		$value->attribute_id = $arow['id'];
		foreach ($value->findAll() as $val) {
			$i++;
			$matches = array();
			//from http://www.w3.org/International/questions/qa-forms-utf-8
			$re =
				'/\A([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} 
			|  \xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}       
			|  \xF4[\x80-\x8F][\x80-\xBF]{2})*\z/x';
			if (!preg_match($re,$val['value_text'])) {
				print "=================================================\n";
				//whitelist
				$new = preg_replace('/[^A-Za-z.,;\-\/()\'" 0-9]/',' ',$val['value_text']);
				$tally[$coll['ascii_id']]++;
				print "->old\n";
				print($val['value_text']);
				print "\n->new\n";
				print "$new\n";
				print "=========== " . $coll['ascii_id'] . " : " .$tally[$coll['ascii_id']] ." =====================" . "\n";
				$v = new Dase_DB_Value;
				$v->load($val['id']);
				$v->value_text = $new;
				$v->update();
			}
		}
	}
}

asort($tally);
print_r($tally);
