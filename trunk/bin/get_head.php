#!/usr/bin/php
<?php
include 'cli_setup.php';

$h = new Dase_Remote('https://webspace.utexas.edu/keanepj/www/peter_keane/another_kind_of_blue/fools_paradise.mp3','','','HEAD');
print($h->get());
