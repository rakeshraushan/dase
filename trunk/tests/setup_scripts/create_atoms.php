<?php

include 'config.php';

$eid = 'pkeane';

$sets_url = 'http://quickdraw.laits.utexas.edu/dase1/user/'.$eid.'/sets.atom';

$user = 'pkeane';
$pass = 'okthen';
$auth = base64_encode($user.':'.$pass);
$header = array("Authorization: Basic $auth");
$opts = array( 'http' => array ('method'=>'GET','header'=>$header));
$ctx = stream_context_create($opts);

print file_get_contents($sets_url,false,$ctx);

