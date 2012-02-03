<?php

include 'config.php';

$sql = "
CREATE TABLE IF NOT EXISTS `set_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$dbh = $db->getDbh();
$sth = $dbh->prepare($sql);
print_r($sth->execute());
