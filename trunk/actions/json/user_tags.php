<?php

if (isset($params['eid'])) {
	$user = Dase_User::get($params['eid']);
	$tpl = new Dase_Json_Template;
	print_r($user->getTags());exit;
	$tpl->setJson($user->getTags());
	$tpl->display();
}
