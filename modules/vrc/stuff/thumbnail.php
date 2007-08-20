<?php
 
/*
	A simple example demonstrate thumbnail creation.
*/ 

$img = file_get_contents('http://dase:api@quickdraw.laits.utexas.edu/vrc/get/99-03638');
file_put_contents('my.tif',$img);
 
/* Create the Imagick object */
$im = new Imagick();
 
/* Read the image file */
$im->readImage( 'my.tif' );
 
/* Thumbnail the image ( width 100, preserve dimensions ) */
$im->thumbnailImage( 100, null );
 
/* Write the thumbail to disk */
$im->writeImage( 'my.png' );
 
/* Free resources associated to the Imagick object */
$im->destroy();
 
?>
