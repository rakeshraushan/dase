<?php
require_once 'cli_setup.php';

$url = "http://quickdraw.laits.utexas.edu/dase/api/xml/efossils_collection";
$remote = new Dase_Remote($url,'xxx','xxx');
print ($remote->get());
