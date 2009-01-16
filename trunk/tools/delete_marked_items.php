#!/usr/bin/php
<?php

include 'config.php';

print "enter collection ascii id: ";
$coll = trim(fgets(STDIN)); 
$count = getMarkedCount($coll);
print "\nthere are $count items to be deleted\n\n";
print "delete these items now? [y/N] ";
$resp = trim(fgets(STDIN)); 
if ('Y' == $resp || 'y' == $resp) {
	echo 'Username: ';
	$user = trim(fgets(STDIN)); 
	echo 'Password: ';
	$pass = preg_replace('/\r?\n$/', '', `stty -echo; head -n1 ; stty echo`);
	$lines = file_get_contents(APP_ROOT.'/collection/'.$coll.'/items/marked/to_be_deleted.uris');
	$i = 0;
	foreach (explode("\n",$lines) as $line) {
		$line = trim($line);
		if ($line) {
			$i++;
			print "DELETE: "; 
			print deleteUrl($line,$user,$pass);
			print " ".$line."\n";
		}
	}
	print "\n\n$i items deleted\n\n";
}



function getMarkedCount($coll)
{
	$set = file_get_contents(APP_ROOT.'/collection/'.$coll.'/items/marked/to_be_deleted.uris');
	$count = preg_match_all("/\n/",$set,$matches);
	return $count;
}

function deleteUrl($url,$user,$pass) 
{
	$ch = curl_init();
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	//print $response."\n";
	$info = curl_getinfo($ch);
	curl_close($ch);
	return $info['http_code'];
}

