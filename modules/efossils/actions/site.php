<?php

$collection = new Dase_DB_Collection;
$collection->ascii_id = 'efossils_collection';
$collection->findOne();


$page['site'] = $params['site'];
$page['section'] = $params['section'];
$page['level'] = $params['level'] ? $params['level'] : 'begin';

$page['text']['basic'] = "
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	Whole lot of text aimed at beginning users.
	";


$tpl = Dase_Template::instance('efossils');
$tpl->assign('page',$page);
$tpl->display('index.tpl');

exit;

