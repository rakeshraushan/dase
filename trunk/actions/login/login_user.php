<?php

//check to see that the get from the login module is in order
//and if so , create the form which XHR will submit
if ($params['token'] != md5($params['eid'] . Dase::getConf('token'))) {
	Dase::error(401);
} else {
	$t = new Dase_Xslt(XSLT_PATH.'login/form.xsl',XSLT_PATH.'login/form.xml');
	$t->set('username',$params['eid']);
	$t->set('password',$params['token']);
	Dase::display($t->transform());
}
