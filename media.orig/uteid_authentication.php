<?php

$ut_user = null;
if (!extension_loaded("eid")) {
	dl("eid.so");
	if (!extension_loaded("eid")) {
		die('no go eid module');
	}
}

$ut_user = eid_decode(); 

if (EID_ERR_OK != $ut_user->status) {
	unset($ut_user);
}

$eid = $ut_user->eid;


