<?php

if (isset($params['eid'])) {
	$user = Dase_User::get($params['eid']);
	$tpl = new Dase_Json_Template;
	$tpl->setJson($user->getData());
	$tpl->display();
}
