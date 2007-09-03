<?php
$database = 'dase_prod';
require_once 'cli_setup.php';

$conf = array();
include DASE_PATH . '/inc/config.php';


function trans($str,$type) {
	if ('display' == $type) {
		return ucwords(str_replace('_',' ',$str));
	}
	if ('ascii' == $type) {
		return strtolower(str_replace(' ','_',$str));
	}
}

foreach ($conf['superuser'] as $super_eid) {
	$super = new Dase_DB_DaseUser;
	$super->eid = $super_eid;
	if (!$super->findOne()) {
		$super->eid = $super_eid;
		$super->name = $super_eid;
		$super->insert();
		print "created superuser $super_eid\n";
	}
}

foreach ($conf['item_status'] as $status) {
	$st = new Dase_DB_ItemStatus;
	$st->status = $v;
	if (!$st->findOne()) {
		$st->insert();
	}
}

foreach ($conf['tag_type'] as $ascii) {
	$tt = new Dase_DB_TagType;
	$tt->ascii_id = $v;
	if (!$tt->findOne()) {
		$tt->name = trans($v,'display');
		$tt->id = $k;
		$tt->insert();
	}
}

foreach ($conf['html_input_type'] as $name) {
	$hit = new Dase_DB_HtmlInputType;
	$hit->name = $name;
	if (!$hit->findOne()) {
		$hit->insert();
	}
}
