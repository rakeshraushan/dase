<?php

include 'cli_setup.php';

$f = Dase_File::newFile('/mnt/home/pkeane/sdase/images/unavail.jpg');
print $f->getSize();
