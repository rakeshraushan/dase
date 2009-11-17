<?php

$c = 'test';

include 'config.php';
$solr = new Dase_SearchEngine_Solr($db,$config);
$res = $solr->scrubIndex($c);

