<?php

include 'config.php';

$sql = "
CREATE TABLE `user` (
	`id` int(11) NOT NULL auto_increment,
	`eid` varchar(40) collate utf8_unicode_ci NOT NULL,
	`name` varchar(40) collate utf8_unicode_ci default NULL,
	`email` varchar(200) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";

print_r(Dase_DBO::query($db,$sql));
