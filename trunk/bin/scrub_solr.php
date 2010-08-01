<?php

$c = 'test';

include 'config.php';
$solr = new Dase_Solr_Search($db,$config);
$res = $solr->scrubIndex($c);

