#!/usr/bin/php
<?php

//note: this is a hack for backwards compat with Pear DB_DataObjects

include 'config.php';

foreach (Dase_DB::listTables() as $table) {
	$sql = "
		ALTER TABLE $table
		ALTER id 
		SET DEFAULT nextval('public.{$table}_seq'::text)
		";
	Dase_DBO::query($sql);
	$sql2 = "
		CREATE SEQUENCE {$table}_seq
		";
	Dase_DBO::query($sql2);
}

