<?php

$acl = file_get_contents('http://pkeane:skeletonkey#!99@quickdraw.laits.utexas.edu/dase1/acl?as_php=1');
file_put_contents('acl.php',"<?php\n\$acl=".$acl.';');

$sources = file_get_contents('http://pkeane:skeletonkey#!99@quickdraw.laits.utexas.edu/dase1/sources?as_php=1');
file_put_contents('sources.php',"<?php\n\$sources=".$sources.';');

