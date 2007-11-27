#!/usr/bin/php
<?php
include 'cli_setup.php';
$sernum = '03-03941';

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);
$sql = "
SELECT * 
FROM tblAccession 
WHERE acc_num_PK = '$sernum'
";

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
	print_r($row);
}

