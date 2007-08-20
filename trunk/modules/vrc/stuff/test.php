<?php


$img = file_get_contents('http://dase:api@quickdraw.laits.utexas.edu/vrc/get/99-03638');

file_put_contents('my.tif',$img);
