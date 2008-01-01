<?php

if (isset($params['eid'])) {
	if ($params['eid'] == Dase_User::getCurrent()) {
		Dase::reload('/',"welcome {$params['eid']} is logged in");
	} else {
		Dase::reload('login');
	}
}
