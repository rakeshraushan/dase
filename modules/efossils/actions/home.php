<?php


$site['name'] = "Hadar";

$site['text']['basic'] = "
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
$tpl->assign('msg','hello world');
$tpl->assign('site',$site);
$tpl->display('index.tpl');
exit;

